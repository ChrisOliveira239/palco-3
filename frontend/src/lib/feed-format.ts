import type { FeedPost } from '@/types/feed-post'

export function nomeDoAutor(post: FeedPost): string {
  return 'art_nome_artistico' in post.autor ? post.autor.art_nome_artistico : post.autor.gro_nome
}
