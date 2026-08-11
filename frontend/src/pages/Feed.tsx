import { useEffect, useState } from 'react'
import { api } from '@/services/api'
import { nomeDoAutor } from '@/lib/feed-format'
import type { FeedPost } from '@/types/feed-post'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const ROTULO_TIPO: Record<FeedPost['fee_tipo'], string> = {
  ATUALIZACAO: 'Atualização',
  FOTO: 'Foto',
  VIDEO: 'Vídeo',
  EVENTO: 'Evento',
}

export function Feed() {
  const [posts, setPosts] = useState<FeedPost[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .get<{ feed: { data: FeedPost[] } }>('/feed')
      .then((response) => setPosts(response.data.feed.data))
      .finally(() => setLoading(false))
  }, [])

  return (
    <div className="mx-auto flex max-w-2xl flex-col gap-6 p-8">
      <h1 className="text-2xl font-semibold">Feed</h1>

      {loading && <p className="text-muted-foreground text-sm">Carregando feed...</p>}
      {!loading && posts.length === 0 && (
        <p className="text-muted-foreground text-sm">
          Nenhuma novidade por aqui. Siga artistas e grupos para ver publicações no seu feed.
        </p>
      )}

      <div className="flex flex-col gap-4">
        {posts.map((post) => (
          <Card key={post.id}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-base">
                {nomeDoAutor(post)}
                <span className="text-muted-foreground text-xs font-normal">
                  {ROTULO_TIPO[post.fee_tipo]}
                </span>
              </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-2 text-sm">
              {post.fee_conteudo && <p className="whitespace-pre-line">{post.fee_conteudo}</p>}
              {post.fee_midia_url && post.fee_tipo === 'FOTO' && (
                <img src={post.fee_midia_url} alt="" className="max-h-96 rounded object-cover" />
              )}
              {post.fee_midia_url && post.fee_tipo === 'VIDEO' && (
                <a href={post.fee_midia_url} target="_blank" rel="noreferrer" className="underline">
                  Ver vídeo
                </a>
              )}
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}
