\set ON_ERROR_STOP on
insert into auth.users(id,raw_user_meta_data) values
 ('00000000-0000-0000-0000-000000000001','{"display_name":"Ana", "role":"admin"}'),
 ('00000000-0000-0000-0000-000000000002','{"display_name":"Beto"}'),
 ('00000000-0000-0000-0000-000000000003','{"display_name":"Admin"}');
update public.profiles set role='admin' where id='00000000-0000-0000-0000-000000000003';
do $$ begin
 if (select role from public.profiles where id='00000000-0000-0000-0000-000000000001') <> 'usuario' then raise exception 'Metadata spoofed admin role'; end if;
end $$;
set role authenticated;
set request.jwt.claim.sub='00000000-0000-0000-0000-000000000001';
insert into public.recipes(id,author_id,title,description,ingredients,steps) values ('10000000-0000-0000-0000-000000000001',auth.uid(),'Receta A','Descripción',array['Ingrediente'],array['Paso']);
insert into public.favorites(user_id,recipe_id) values (auth.uid(),'10000000-0000-0000-0000-000000000001');
insert into public.ratings(user_id,recipe_id,score) values (auth.uid(),'10000000-0000-0000-0000-000000000001',5);
insert into public.comments(user_id,recipe_id,body) values (auth.uid(),'10000000-0000-0000-0000-000000000001','Muy rica');
update public.profiles set display_name='Ana actualizada' where id=auth.uid();
do $$ begin
 begin update public.profiles set role='admin' where id=auth.uid(); raise exception 'Role escalation allowed'; exception when insufficient_privilege then null; end;
 begin update public.profiles set disabled=true where id=auth.uid(); raise exception 'Self disable allowed'; exception when insufficient_privilege then null; end;
 begin perform public.admin_update_user('00000000-0000-0000-0000-000000000002','admin',false); raise exception 'Unprivileged RPC allowed'; exception when insufficient_privilege then null; end;
 begin insert into public.ratings values(auth.uid(),'10000000-0000-0000-0000-000000000001',6); raise exception 'Invalid score allowed'; exception when check_violation then null; end;
end $$;
insert into storage.objects(bucket_id,name) values('recipe-images',auth.uid()::text||'/photo.jpg');
set request.jwt.claim.sub='00000000-0000-0000-0000-000000000002';
do $$ declare affected integer; begin
 if (select count(*) from public.favorites) <> 0 then raise exception 'Private favorites leaked'; end if;
 update public.recipes set title='Stolen' where id='10000000-0000-0000-0000-000000000001'; get diagnostics affected=row_count; if affected<>0 then raise exception 'Other recipe editable'; end if;
 delete from public.comments; get diagnostics affected=row_count; if affected<>0 then raise exception 'Other comment deletable'; end if;
 begin insert into public.recipes(author_id,title,description,ingredients,steps) values('00000000-0000-0000-0000-000000000001','Spoof','D',array['A'],array['B']); raise exception 'Spoofed author allowed'; exception when insufficient_privilege then null; end;
 begin insert into storage.objects(bucket_id,name) values('recipe-images','00000000-0000-0000-0000-000000000001/spoof.jpg'); raise exception 'Other storage folder writable'; exception when insufficient_privilege then null; end;
 delete from storage.objects; get diagnostics affected=row_count; if affected<>0 then raise exception 'Other storage folder deletable'; end if;
end $$;
set role anon;
set request.jwt.claim.sub='';
do $$ begin
 if (select count(*) from public.recipes)<>1 then raise exception 'Anonymous catalogue broken'; end if;
 begin insert into public.comments(user_id,recipe_id,body) values('00000000-0000-0000-0000-000000000002','10000000-0000-0000-0000-000000000001','Anonymous'); raise exception 'Anonymous write allowed'; exception when insufficient_privilege then null; end;
 begin perform public.admin_delete_user('00000000-0000-0000-0000-000000000001'); raise exception 'Anonymous admin RPC allowed'; exception when insufficient_privilege then null; end;
end $$;
set role authenticated;
set request.jwt.claim.sub='00000000-0000-0000-0000-000000000003';
select public.admin_update_user('00000000-0000-0000-0000-000000000002','usuario',true);
update public.recipes set title='Moderated' where id='10000000-0000-0000-0000-000000000001';
do $$ begin
 if (select title from public.recipes limit 1)<>'Moderated' then raise exception 'Admin moderation failed'; end if;
 begin perform public.admin_update_user(auth.uid(),'usuario',false); raise exception 'Admin self-demotion allowed'; exception when raise_exception then if sqlerrm='Admin self-demotion allowed' then raise; end if; end;
end $$;
set request.jwt.claim.sub='00000000-0000-0000-0000-000000000002';
do $$ begin
 if public.is_active_user() then raise exception 'Disabled account is active'; end if;
 begin insert into public.comments(user_id,recipe_id,body) values(auth.uid(),'10000000-0000-0000-0000-000000000001','Disabled'); raise exception 'Disabled write allowed'; exception when insufficient_privilege then null; end;
 begin insert into storage.objects(bucket_id,name) values('recipe-images',auth.uid()::text||'/photo.jpg'); raise exception 'Disabled upload allowed'; exception when insufficient_privilege then null; end;
end $$;
set request.jwt.claim.sub='00000000-0000-0000-0000-000000000003';
select public.admin_delete_user('00000000-0000-0000-0000-000000000001');
reset role;
do $$ begin
 if exists(select 1 from auth.users where id='00000000-0000-0000-0000-000000000001') then raise exception 'User not deleted'; end if;
 if exists(select 1 from public.recipes) or exists(select 1 from public.favorites) or exists(select 1 from public.ratings) or exists(select 1 from public.comments) then raise exception 'Cascade incomplete'; end if;
end $$;
select 'PASS: profile trigger, role protection, anonymous access, ownership, private favorites, score constraints, storage ownership, disabled accounts, admin RPC and deletion cascades' as result;
