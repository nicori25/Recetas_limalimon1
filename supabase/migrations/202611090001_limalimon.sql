-- LimaLimón v2. Apply once using Supabase migrations / SQL Editor.
begin;
create table public.profiles (
 id uuid primary key references auth.users(id) on delete cascade,
 display_name text not null check (char_length(btrim(display_name)) between 1 and 80),
 dietary_preference text not null default '' check (char_length(dietary_preference) <= 100),
 role text not null default 'usuario' check (role in ('usuario','admin')),
 disabled boolean not null default false,
 legacy_id bigint unique,
 created_at timestamptz not null default now()
);
create or replace function public.is_active_user() returns boolean language sql stable security definer set search_path = '' as $$
 select exists(select 1 from public.profiles where id = auth.uid() and not disabled)
$$;
create or replace function public.is_admin() returns boolean language sql stable security definer set search_path = '' as $$
 select exists(select 1 from public.profiles where id = auth.uid() and role = 'admin' and not disabled)
$$;
create function public.handle_new_user() returns trigger language plpgsql security definer set search_path = '' as $$
begin
 insert into public.profiles(id,display_name) values (new.id, left(coalesce(nullif(btrim(new.raw_user_meta_data->>'display_name'),''),'Cocinero/a'),80));
 return new;
end $$;
revoke all on function public.handle_new_user() from public;
create trigger on_auth_user_created after insert on auth.users for each row execute function public.handle_new_user();

create table public.recipes (
 id uuid primary key default gen_random_uuid(),
 author_id uuid not null references public.profiles(id) on delete cascade,
 title text not null check(char_length(btrim(title)) between 1 and 140),
 description text not null check(char_length(btrim(description)) between 1 and 1500),
 ingredients text[] not null check(cardinality(ingredients) between 1 and 100 and char_length(array_to_string(ingredients,E'\n')) <= 15000),
 steps text[] not null check(cardinality(steps) between 1 and 100 and char_length(array_to_string(steps,E'\n')) <= 30000),
 dietary_tags text[] not null default '{}' check(cardinality(dietary_tags) <= 20 and char_length(array_to_string(dietary_tags,',')) <= 800),
 season text not null default 'todo el año' check(season in ('primavera','verano','otoño','invierno','todo el año')),
 image_path text check(image_path is null or (char_length(image_path) <= 300 and image_path !~ '^(https?:|data:|//)')),
 prep_minutes integer not null default 30 check(prep_minutes between 1 and 2880),
 servings integer not null default 2 check(servings between 1 and 100),
 legacy_id bigint unique,
 created_at timestamptz not null default now()
);
create table public.favorites (user_id uuid not null references public.profiles(id) on delete cascade, recipe_id uuid not null references public.recipes(id) on delete cascade, primary key(user_id,recipe_id));
create table public.ratings (user_id uuid not null references public.profiles(id) on delete cascade, recipe_id uuid not null references public.recipes(id) on delete cascade, score integer not null check(score between 1 and 5), primary key(user_id,recipe_id));
create table public.comments (id uuid primary key default gen_random_uuid(), user_id uuid not null references public.profiles(id) on delete cascade, recipe_id uuid not null references public.recipes(id) on delete cascade, body text not null check(char_length(btrim(body)) between 1 and 2000), legacy_id bigint unique, created_at timestamptz not null default now());
create index recipes_author_idx on public.recipes(author_id);
create index recipes_date_idx on public.recipes(created_at desc);
create index recipes_tags_idx on public.recipes using gin(dietary_tags);
create index comments_recipe_idx on public.comments(recipe_id);
create index favorites_recipe_idx on public.favorites(recipe_id);
create index ratings_recipe_idx on public.ratings(recipe_id);

alter table public.profiles enable row level security;
alter table public.recipes enable row level security;
alter table public.favorites enable row level security;
alter table public.ratings enable row level security;
alter table public.comments enable row level security;
-- Remove Supabase default grants; allow only the operations this client needs.
revoke all on public.profiles,public.recipes,public.favorites,public.ratings,public.comments from anon,authenticated;
grant select on public.profiles,public.recipes,public.ratings,public.comments to anon,authenticated;
grant select,insert,delete on public.favorites to authenticated;
grant insert,update,delete on public.recipes,public.ratings to authenticated;
grant insert,delete on public.comments to authenticated;
grant update(display_name,dietary_preference) on public.profiles to authenticated;
grant all on public.profiles,public.recipes,public.favorites,public.ratings,public.comments to service_role;

create policy profiles_read on public.profiles for select using(true);
create policy profiles_edit_self on public.profiles for update to authenticated using(id=auth.uid() and public.is_active_user()) with check(id=auth.uid() and public.is_active_user());
create policy recipes_read on public.recipes for select using(true);
create policy recipes_create on public.recipes for insert to authenticated with check(author_id=auth.uid() and public.is_active_user());
create policy recipes_edit on public.recipes for update to authenticated using((author_id=auth.uid() and public.is_active_user()) or public.is_admin()) with check((author_id=auth.uid() and public.is_active_user()) or public.is_admin());
create policy recipes_remove on public.recipes for delete to authenticated using((author_id=auth.uid() and public.is_active_user()) or public.is_admin());
create policy favorites_read on public.favorites for select to authenticated using(user_id=auth.uid() and public.is_active_user());
create policy favorites_create on public.favorites for insert to authenticated with check(user_id=auth.uid() and public.is_active_user());
create policy favorites_remove on public.favorites for delete to authenticated using(user_id=auth.uid() and public.is_active_user());
create policy ratings_read on public.ratings for select using(true);
create policy ratings_create on public.ratings for insert to authenticated with check(user_id=auth.uid() and public.is_active_user());
create policy ratings_edit on public.ratings for update to authenticated using(user_id=auth.uid() and public.is_active_user()) with check(user_id=auth.uid() and public.is_active_user());
create policy ratings_remove on public.ratings for delete to authenticated using((user_id=auth.uid() and public.is_active_user()) or public.is_admin());
create policy comments_read on public.comments for select using(true);
create policy comments_create on public.comments for insert to authenticated with check(user_id=auth.uid() and public.is_active_user());
create policy comments_remove on public.comments for delete to authenticated using((user_id=auth.uid() and public.is_active_user()) or public.is_admin());

create function public.admin_update_user(target_id uuid,new_role text,new_disabled boolean) returns void language plpgsql security definer set search_path = '' as $$
begin
 if not public.is_admin() then raise exception 'Solo un administrador puede realizar esta acción' using errcode='42501'; end if;
 if target_id=auth.uid() then raise exception 'No podés modificar tus propios permisos'; end if;
 if new_role not in ('admin','usuario') or new_role is null or new_disabled is null then raise exception 'Parámetros inválidos'; end if;
 update public.profiles set role=new_role,disabled=new_disabled where id=target_id;
 if not found then raise exception 'El usuario no existe'; end if;
end $$;
create function public.admin_delete_user(target_id uuid) returns void language plpgsql security definer set search_path = '' as $$
begin
 if not public.is_admin() then raise exception 'Solo un administrador puede realizar esta acción' using errcode='42501'; end if;
 if target_id=auth.uid() then raise exception 'No podés eliminar tu propia cuenta'; end if;
 delete from auth.users where id=target_id;
 if not found then raise exception 'El usuario no existe'; end if;
end $$;
revoke all on function public.admin_update_user(uuid,text,boolean),public.admin_delete_user(uuid) from public,anon;
grant execute on function public.admin_update_user(uuid,text,boolean),public.admin_delete_user(uuid) to authenticated;
revoke all on function public.is_admin(),public.is_active_user() from public;
grant execute on function public.is_admin(),public.is_active_user() to anon,authenticated,service_role;

insert into storage.buckets(id,name,public,file_size_limit,allowed_mime_types)
 values('recipe-images','recipe-images',true,5242880,array['image/jpeg','image/png','image/webp']);
create policy recipe_images_read on storage.objects for select using(bucket_id='recipe-images');
create policy recipe_images_insert on storage.objects for insert to authenticated with check(bucket_id='recipe-images' and (storage.foldername(name))[1]=auth.uid()::text and public.is_active_user());
create policy recipe_images_delete on storage.objects for delete to authenticated using(bucket_id='recipe-images' and (((storage.foldername(name))[1]=auth.uid()::text and public.is_active_user()) or public.is_admin()));
commit;
