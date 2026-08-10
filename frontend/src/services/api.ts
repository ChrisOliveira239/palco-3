import axios, { type InternalAxiosRequestConfig } from 'axios'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
})

export function attachAuthToken(config: InternalAxiosRequestConfig) {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.set('Authorization', `Bearer ${token}`)
  }
  return config
}

api.interceptors.request.use(attachAuthToken)
