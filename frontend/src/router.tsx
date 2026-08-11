import { createBrowserRouter, type RouteObject } from 'react-router'
import { RootLayout } from '@/layouts/RootLayout'
import { GuestOnly } from '@/components/GuestOnly'
import { RequireAuth } from '@/components/RequireAuth'
import { Home } from '@/pages/Home'
import { Login } from '@/pages/Login'
import { Register } from '@/pages/Register'
import { Profile } from '@/pages/Profile'
import { EventDetail } from '@/pages/EventDetail'
import { Feed } from '@/pages/Feed'
import { NotFound } from '@/pages/NotFound'

export const routes: RouteObject[] = [
  {
    path: '/',
    element: <RootLayout />,
    children: [
      { index: true, element: <Home /> },
      { path: 'eventos/:id', element: <EventDetail /> },
      {
        element: <GuestOnly />,
        children: [
          { path: 'login', element: <Login /> },
          { path: 'register', element: <Register /> },
        ],
      },
      {
        element: <RequireAuth />,
        children: [
          { path: 'perfil', element: <Profile /> },
          { path: 'feed', element: <Feed /> },
        ],
      },
      { path: '*', element: <NotFound /> },
    ],
  },
]

export const router = createBrowserRouter(routes)
