/** Resolve bundled public assets under the deployment path (including GitHub Pages). */
export function publicAsset(path: string, base = import.meta.env.BASE_URL) {
  return `${base.replace(/\/?$/, '/')}${path.replace(/^\/+/, '')}`
}

/** Auth must return to the application's base, before Vue Router's hash. */
export function authRedirectUrl(origin: string, base = import.meta.env.BASE_URL) {
  return new URL(base, origin).href
}
