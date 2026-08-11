import { useState, type FormEvent } from 'react'
import { api } from '@/services/api'
import { useAuth } from '@/context/AuthContext'
import { getErrorMessage, getFieldErrors } from '@/lib/api-errors'
import type { User } from '@/types/user'
import type { ArtistProfile } from '@/types/artist-profile'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Alert, AlertDescription } from '@/components/ui/alert'

function FieldError({ messages }: { messages?: string[] }) {
  return (
    <>
      {messages?.map((message) => (
        <p key={message} className="text-destructive text-sm">
          {message}
        </p>
      ))}
    </>
  )
}

function ProfileForm({ user }: { user: User }) {
  const { updateUser } = useAuth()

  const [useName, setUseName] = useState(user.use_name)
  const [useBio, setUseBio] = useState(user.use_bio ?? '')
  const [useCity, setUseCity] = useState(user.use_city ?? '')
  const [useState_, setUseState] = useState(user.use_state ?? '')
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [generalError, setGeneralError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent) {
    event.preventDefault()
    setFieldErrors({})
    setGeneralError(null)
    setSuccess(false)
    setSubmitting(true)

    try {
      const response = await api.patch<{ user: User }>('/profile', {
        use_name: useName,
        use_bio: useBio || null,
        use_city: useCity || null,
        use_state: useState_ || null,
      })
      updateUser(response.data.user)
      setSuccess(true)
    } catch (error) {
      setFieldErrors(getFieldErrors(error))
      setGeneralError(getErrorMessage(error) ?? 'Não foi possível salvar seus dados.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Meus dados</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
          {generalError && (
            <Alert variant="destructive">
              <AlertDescription>{generalError}</AlertDescription>
            </Alert>
          )}
          {success && (
            <Alert>
              <AlertDescription>Dados atualizados.</AlertDescription>
            </Alert>
          )}

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="use_name">Nome</Label>
            <Input id="use_name" value={useName} onChange={(event) => setUseName(event.target.value)} required />
            <FieldError messages={fieldErrors.use_name} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="use_bio">Bio</Label>
            <Textarea id="use_bio" value={useBio} onChange={(event) => setUseBio(event.target.value)} />
            <FieldError messages={fieldErrors.use_bio} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="use_city">Cidade</Label>
            <Input id="use_city" value={useCity} onChange={(event) => setUseCity(event.target.value)} />
            <FieldError messages={fieldErrors.use_city} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="use_state">Estado</Label>
            <Input id="use_state" value={useState_} onChange={(event) => setUseState(event.target.value)} />
            <FieldError messages={fieldErrors.use_state} />
          </div>

          <Button type="submit" disabled={submitting}>
            Salvar dados
          </Button>
        </form>
      </CardContent>
    </Card>
  )
}

function ArtistProfileForm({ user }: { user: User }) {
  const { updateUser } = useAuth()
  const artistProfile = user.artist_profile

  const [nomeArtistico, setNomeArtistico] = useState(artistProfile?.art_nome_artistico ?? '')
  const [bio, setBio] = useState(artistProfile?.art_bio ?? '')
  const [drt, setDrt] = useState(artistProfile?.art_drt ?? '')
  const [telefone, setTelefone] = useState(artistProfile?.art_telefone ?? '')
  const [email, setEmail] = useState(artistProfile?.art_email ?? '')
  const [site, setSite] = useState(artistProfile?.art_site ?? '')
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [generalError, setGeneralError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  async function handleSubmit(event: FormEvent) {
    event.preventDefault()
    setFieldErrors({})
    setGeneralError(null)
    setSuccess(false)
    setSubmitting(true)

    const payload = {
      art_nome_artistico: nomeArtistico,
      art_bio: bio || null,
      art_drt: drt || null,
      art_telefone: telefone || null,
      art_email: email || null,
      art_site: site || null,
    }

    try {
      const response = artistProfile
        ? await api.patch<{ artist_profile: ArtistProfile }>(`/artist-profiles/${artistProfile.id}`, payload)
        : await api.post<{ artist_profile: ArtistProfile }>('/artist-profiles', payload)

      updateUser({ ...user, artist_profile: response.data.artist_profile })
      setSuccess(true)
    } catch (error) {
      setFieldErrors(getFieldErrors(error))
      setGeneralError(getErrorMessage(error) ?? 'Não foi possível salvar o perfil de artista.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Perfil de artista</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
          {generalError && (
            <Alert variant="destructive">
              <AlertDescription>{generalError}</AlertDescription>
            </Alert>
          )}
          {success && (
            <Alert>
              <AlertDescription>Perfil de artista salvo.</AlertDescription>
            </Alert>
          )}

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="art_nome_artistico">Nome artístico</Label>
            <Input
              id="art_nome_artistico"
              value={nomeArtistico}
              onChange={(event) => setNomeArtistico(event.target.value)}
              required
            />
            <FieldError messages={fieldErrors.art_nome_artistico} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="art_bio">Bio</Label>
            <Textarea id="art_bio" value={bio} onChange={(event) => setBio(event.target.value)} />
            <FieldError messages={fieldErrors.art_bio} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="art_drt">DRT</Label>
            <Input id="art_drt" value={drt} onChange={(event) => setDrt(event.target.value)} />
            <FieldError messages={fieldErrors.art_drt} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="art_telefone">Telefone</Label>
            <Input id="art_telefone" value={telefone} onChange={(event) => setTelefone(event.target.value)} />
            <FieldError messages={fieldErrors.art_telefone} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="art_email">E-mail de contato</Label>
            <Input
              id="art_email"
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
            />
            <FieldError messages={fieldErrors.art_email} />
          </div>

          <div className="flex flex-col gap-1.5">
            <Label htmlFor="art_site">Site</Label>
            <Input id="art_site" value={site} onChange={(event) => setSite(event.target.value)} />
            <FieldError messages={fieldErrors.art_site} />
          </div>

          <Button type="submit" disabled={submitting}>
            {artistProfile ? 'Salvar perfil de artista' : 'Criar perfil de artista'}
          </Button>
        </form>
      </CardContent>
    </Card>
  )
}

export function Profile() {
  const { user } = useAuth()

  if (!user) return null

  return (
    <div className="mx-auto flex max-w-sm flex-col gap-6 p-8">
      <ProfileForm user={user} />
      <ArtistProfileForm user={user} />
    </div>
  )
}
