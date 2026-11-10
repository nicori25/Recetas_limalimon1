export const DIETS = ['vegano', 'vegetariano', 'sin gluten', 'sin lactosa'] as const
export const SEASONS = ['primavera', 'verano', 'otoño', 'invierno', 'todo el año'] as const
export interface Profile {
  id: string
  display_name: string
  dietary_preference: string
  role: 'usuario' | 'admin'
  disabled: boolean
  legacy_id?: number
}
export interface Recipe {
  id: string
  author_id: string
  title: string
  description: string
  ingredients: string[]
  steps: string[]
  dietary_tags: string[]
  season: string
  image_path: string | null
  prep_minutes: number
  servings: number
  created_at: string
  legacy_id?: number
}
export interface Comment {
  id: string
  recipe_id: string
  user_id: string
  body: string
  created_at: string
}
export interface Rating {
  recipe_id: string
  user_id: string
  score: number
}
export interface Favorite {
  recipe_id: string
  user_id: string
}
export interface ShoppingItem {
  id: string
  text: string
  checked: boolean
  recipe: string
}
