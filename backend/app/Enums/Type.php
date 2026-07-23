<?php

namespace App\Enums;

enum TipoConta: string
{
    case PESSOA = 'pessoa';
    case EMPRESA = 'empresa';
}

enum GroupMemberRole: string
{
    case ADMIN = 'admin';
    case MEMBRO = 'membro';
}

enum EventStatus: string
{
    case RASCUNHO = 'rascunho';
    case PENDENTE = 'pendente';
    case APROVADO = 'aprovado';
    case REJEITADO = 'rejeitado';
    case PUBLICADO = 'publicado';
}

enum EventMediaType: string
{
    case FOTO = 'foto';
    case VIDEO = 'video';
}

enum TicketStatus: string
{
    case VALIDO = 'valido';
    case USADO = 'usado';
    case CANCELADO = 'cancelado';
}

enum TipoApoio: string
{
    case DINHEIRO = 'dinheiro';
    case EQUIPAMENTO = 'equipamento';
    case FIGURINO = 'figurino';
    case ALIMENTACAO = 'alimentacao';
    case TRANSPORTE = 'transporte';
    case HOSPEDAGEM = 'hospedagem';
    case FOTOGRAFIA = 'fotografia';
    case FILMAGEM = 'filmagem';
    case ILUMINACAO = 'iluminacao';
    case SOM = 'som';
    case OUTRO = 'outro';
}

enum SponsorshipStatus: string
{
    case PROPOSTO = 'proposto';
    case ACEITO = 'aceito';
    case RECUSADO = 'recusado';
    case CONCLUIDO = 'concluido';
}

enum FeedPostType: string
{
    case ATUALIZACAO = 'atualizacao';
    case FOTO = 'foto';
    case VIDEO = 'video';
    case EVENTO = 'evento';
}
