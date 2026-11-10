import type { Recipe, Profile, Rating } from '../lib/types'
export const demoProfile: Profile = {
  id: 'demo-user',
  display_name: 'Alex',
  dietary_preference: 'vegetariano',
  role: 'admin',
  disabled: false,
}
export const demoAuthors: Profile[] = [
  demoProfile,
  {
    id: 'demo-chef',
    display_name: 'Comunidad LimaLimón',
    dietary_preference: '',
    role: 'usuario',
    disabled: false,
  },
]
const base = { author_id: 'demo-chef', created_at: '2026-11-01T12:00:00Z' }
export const demoRecipes: Recipe[] = [
  {
    ...base,
    id: 'budin-banana',
    title: 'Budín de banana',
    description:
      'El de siempre, un poquito más liviano. Húmedo, esponjoso y perfecto para acompañar unos mates.',
    ingredients: [
      '2 bananas maduras',
      '2 huevos',
      '200 g de harina leudante',
      '100 ml de leche sin lactosa',
      '100 g de azúcar',
      '1 cucharadita de esencia de vainilla',
    ],
    steps: [
      'Precalentá el horno a 180 °C y aceitá un molde de budín.',
      'Pisá las bananas y mezclalas con los huevos, el azúcar y la vainilla.',
      'Agregá la leche y después la harina, con movimientos envolventes.',
      'Volcá en el molde y horneá entre 35 y 40 minutos. Comprobá con un palillo antes de retirar.',
    ],
    dietary_tags: ['sin lactosa', 'vegetariano'],
    season: 'otoño',
    image_path: '/images/budin.svg',
    prep_minutes: 50,
    servings: 8,
  },
  {
    ...base,
    id: 'ensalada-primavera',
    title: 'Ensalada de primavera',
    description:
      'Colores, frescura y un aderezo de limón que une todo. Un almuerzo simple que se siente bien.',
    ingredients: [
      '1 taza de garbanzos cocidos',
      '1 pepino',
      '200 g de tomates cherry',
      '1 palta',
      'Jugo de 1 limón',
      '2 cucharadas de aceite de oliva',
      'Sal a gusto',
    ],
    steps: [
      'Lavá los vegetales y cortalos en trozos pequeños.',
      'Enjuagá los garbanzos y mezclalos con los vegetales.',
      'Batí el limón con el aceite y la sal. Condimentá justo antes de servir.',
    ],
    dietary_tags: ['vegano', 'vegetariano', 'sin gluten', 'sin lactosa'],
    season: 'primavera',
    image_path: '/images/ensalada.svg',
    prep_minutes: 15,
    servings: 2,
  },
  {
    ...base,
    id: 'pasta-verde',
    title: 'Pasta con pesto de espinaca',
    description:
      'Una vuelta verde a tu plato favorito. Cremosa, fácil y lista en lo que tarda el agua en hervir.',
    ingredients: [
      '200 g de pasta seca sin huevo',
      '2 tazas de espinaca fresca',
      '3 cucharadas de semillas de girasol',
      '1 diente de ajo',
      '3 cucharadas de aceite de oliva',
      'Sal y pimienta',
    ],
    steps: [
      'Cociná la pasta según las instrucciones del paquete.',
      'Procesá la espinaca con las semillas, el ajo y el aceite.',
      'Agregá un poco del agua de cocción para aligerar el pesto.',
      'Mezclá la pasta con el pesto y serví caliente.',
    ],
    dietary_tags: ['vegano', 'vegetariano', 'sin lactosa'],
    season: 'todo el año',
    image_path: '/images/pasta.svg',
    prep_minutes: 25,
    servings: 2,
  },
  {
    ...base,
    id: 'bowl-calabaza',
    title: 'Bowl tibio de calabaza',
    description:
      'Calabaza dorada, quinoa y muchas ganas de comer rico. Para esos días que piden algo reconfortante.',
    ingredients: [
      '400 g de calabaza',
      '1 taza de quinoa',
      '2 tazas de agua',
      '1 puñado de rúcula',
      '2 cucharadas de aceite de oliva',
      'Pimentón y sal',
    ],
    steps: [
      'Cortá la calabaza en cubos, condimentá y horneá a 200 °C por 25 minutos.',
      'Lavá la quinoa y cociná con el agua hasta que se absorba.',
      'Serví la quinoa con la calabaza y la rúcula fresca.',
    ],
    dietary_tags: ['vegano', 'vegetariano', 'sin gluten', 'sin lactosa'],
    season: 'invierno',
    image_path: '/images/bowl.svg',
    prep_minutes: 35,
    servings: 2,
  },
  {
    ...base,
    id: 'tostadas-palta',
    title: 'Tostadas de palta y limón',
    description:
      'Tu pausa favorita del día. Pan crocante, palta cremosa y ese toque cítrico que cambia todo.',
    ingredients: [
      '2 rebanadas de pan sin gluten certificado',
      '1 palta madura',
      'Jugo de medio limón',
      '1 tomate',
      'Sal y semillas a gusto',
    ],
    steps: [
      'Tostá el pan hasta que quede dorado.',
      'Pisá la palta con el limón y la sal.',
      'Untá las tostadas y terminá con tomate y semillas.',
    ],
    dietary_tags: ['vegano', 'vegetariano', 'sin gluten', 'sin lactosa'],
    season: 'todo el año',
    image_path: '/images/tostadas.svg',
    prep_minutes: 10,
    servings: 1,
  },
  {
    ...base,
    id: 'avena-frutos',
    title: 'Avena con frutos rojos',
    description:
      'Un desayuno que se prepara mientras dormís. Fresco, suave y con toda la energía para empezar.',
    ingredients: [
      '½ taza de avena',
      '½ taza de bebida vegetal',
      '1 cucharadita de chía',
      '100 g de frutos rojos',
      'Canela a gusto',
    ],
    steps: [
      'Mezclá la avena, la bebida vegetal y la chía en un frasco.',
      'Tapá y refrigerá al menos 6 horas.',
      'Agregá los frutos rojos y la canela antes de comer.',
    ],
    dietary_tags: ['vegano', 'vegetariano', 'sin lactosa'],
    season: 'verano',
    image_path: '/images/avena.svg',
    prep_minutes: 10,
    servings: 1,
  },
]
export const demoRatings: Rating[] = demoRecipes.map((r) => ({
  recipe_id: r.id,
  user_id: 'demo-chef',
  score: 5,
}))
