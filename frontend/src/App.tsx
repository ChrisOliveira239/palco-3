import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

function App() {
  return (
    <div className="flex min-h-svh items-center justify-center p-8">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Palco</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-muted-foreground mb-4 text-sm">
            Setup do frontend (Vite + React + Tailwind + shadcn/ui) funcionando.
          </p>
          <Button>Testar botão</Button>
        </CardContent>
      </Card>
    </div>
  )
}

export default App
