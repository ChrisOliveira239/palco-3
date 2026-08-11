import { createContext, useContext, useEffect, useState, type ReactNode } from 'react'
import { api, setUnauthorizedHandler } from '@/services/api'
import type { User } from '@/types/user'

type AuthStatus = 'idle' | 'loading' | 'authenticated' | 'unauthenticated'

interface RegisterPayload {
  use_name: string
  email: string
  password: string
  password_confirmation: string
  use_tipo_conta?: 'PESSOA' | 'EMPRESA'
}

interface AuthContextValue {
  user: User | null
  status: AuthStatus
  login: (email: string, password: string) => Promise<void>
  register: (payload: RegisterPayload) => Promise<void>
  logout: () => Promise<void>
  updateUser: (user: User) => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [status, setStatus] = useState<AuthStatus>('idle')

  useEffect(() => {
    setUnauthorizedHandler(() => {
      setUser(null)
      setStatus('unauthenticated')
    })
    return () => setUnauthorizedHandler(null)
  }, [])

  useEffect(() => {
    const token = localStorage.getItem('token')
    if (!token) {
      setStatus('unauthenticated')
      return
    }

    setStatus('loading')
    api
      .get<{ user: User }>('/me')
      .then((response) => {
        setUser(response.data.user)
        setStatus('authenticated')
      })
      .catch(() => {
        setStatus('unauthenticated')
      })
  }, [])

  async function login(email: string, password: string) {
    const response = await api.post<{ user: User; token: string }>('/login', { email, password })
    localStorage.setItem('token', response.data.token)
    setUser(response.data.user)
    setStatus('authenticated')
  }

  async function register(payload: RegisterPayload) {
    const response = await api.post<{ user: User; token: string }>('/register', payload)
    localStorage.setItem('token', response.data.token)
    setUser(response.data.user)
    setStatus('authenticated')
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // revoga localmente mesmo se o servidor não responder
    } finally {
      localStorage.removeItem('token')
      setUser(null)
      setStatus('unauthenticated')
    }
  }

  return (
    <AuthContext.Provider value={{ user, status, login, register, logout, updateUser: setUser }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth precisa estar dentro de AuthProvider')
  return context
}
