import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router'
import { useAuth } from '@/context/AuthContext'
import { getErrorMessage, getFieldErrors } from '@/lib/api-errors'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'

export function Register() {
  const { register } = useAuth()
  const navigate = useNavigate()

  const [useName, setUseName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [tipoConta, setTipoConta] = useState<'PESSOA' | 'EMPRESA'>('PESSOA')
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [generalError, setGeneralError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent) {
    event.preventDefault()
    setFieldErrors({})
    setGeneralError(null)
    setSubmitting(true)

    try {
      await register({
        use_name: useName,
        email,
        password,
        password_confirmation: passwordConfirmation,
        use_tipo_conta: tipoConta,
      })
      navigate('/')
    } catch (error) {
      setFieldErrors(getFieldErrors(error))
      setGeneralError(getErrorMessage(error) ?? 'Não foi possível criar a conta.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="flex min-h-svh items-center justify-center p-8">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Criar conta</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            {generalError && (
              <Alert variant="destructive">
                <AlertDescription>{generalError}</AlertDescription>
              </Alert>
            )}

            <div className="flex flex-col gap-1.5">
              <Label htmlFor="use_name">Nome</Label>
              <Input
                id="use_name"
                value={useName}
                onChange={(event) => setUseName(event.target.value)}
                required
              />
              {fieldErrors.use_name?.map((message) => (
                <p key={message} className="text-destructive text-sm">
                  {message}
                </p>
              ))}
            </div>

            <div className="flex flex-col gap-1.5">
              <Label htmlFor="email">E-mail</Label>
              <Input
                id="email"
                type="email"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                required
              />
              {fieldErrors.email?.map((message) => (
                <p key={message} className="text-destructive text-sm">
                  {message}
                </p>
              ))}
            </div>

            <div className="flex flex-col gap-1.5">
              <Label htmlFor="password">Senha</Label>
              <Input
                id="password"
                type="password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
                required
              />
              {fieldErrors.password?.map((message) => (
                <p key={message} className="text-destructive text-sm">
                  {message}
                </p>
              ))}
            </div>

            <div className="flex flex-col gap-1.5">
              <Label htmlFor="password_confirmation">Confirmar senha</Label>
              <Input
                id="password_confirmation"
                type="password"
                value={passwordConfirmation}
                onChange={(event) => setPasswordConfirmation(event.target.value)}
                required
              />
            </div>

            <div className="flex flex-col gap-1.5">
              <Label>Tipo de conta</Label>
              <div className="flex gap-4">
                <Label className="font-normal">
                  <input
                    type="radio"
                    name="use_tipo_conta"
                    checked={tipoConta === 'PESSOA'}
                    onChange={() => setTipoConta('PESSOA')}
                  />
                  Pessoa física
                </Label>
                <Label className="font-normal">
                  <input
                    type="radio"
                    name="use_tipo_conta"
                    checked={tipoConta === 'EMPRESA'}
                    onChange={() => setTipoConta('EMPRESA')}
                  />
                  Empresa
                </Label>
              </div>
            </div>

            <Button type="submit" disabled={submitting}>
              Criar conta
            </Button>

            <p className="text-muted-foreground text-sm">
              Já tem conta?{' '}
              <Link to="/login" className="underline">
                Entrar
              </Link>
            </p>
          </form>
        </CardContent>
      </Card>
    </div>
  )
}
