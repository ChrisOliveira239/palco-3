import { Link } from 'react-router'

export function NotFound() {
  return (
    <div className="flex min-h-svh flex-col items-center justify-center gap-4 p-8">
      <h1 className="text-2xl font-semibold">404</h1>
      <Link to="/" className="underline">
        Voltar
      </Link>
    </div>
  )
}
