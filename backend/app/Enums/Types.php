<?php

namespace App\Enums;

class Types
{
    public const TIPO_CONTA = ['PESSOA', 'EMPRESA'];

    public const GROUP_MEMBER_ROLE = ['ADMIN', 'MEMBRO'];

    public const EVENT_STATUS = ['RASCUNHO', 'PENDENTE', 'APROVADO', 'REJEITADO', 'PUBLICADO'];

    public const EVENT_MEDIA_TYPE = ['FOTO', 'VIDEO'];

    public const TICKET_STATUS = ['VALIDO', 'USADO', 'CANCELADO'];

    public const TIPO_APOIO = [
        'DINHEIRO', 'EQUIPAMENTO', 'FIGURINO', 'ALIMENTACAO',
        'TRANSPORTE', 'HOSPEDAGEM', 'FOTOGRAFIA', 'FILMAGEM', 'ILUMINACAO', 'SOM', 'OUTRO',
    ];

    public const SPONSORSHIP_STATUS = ['PROPOSTO', 'ACEITO', 'RECUSADO', 'CONCLUIDO'];

    public const FEED_POST_TYPE = ['ATUALIZACAO', 'FOTO', 'VIDEO', 'EVENTO'];

    public const OPPORTUNITY_STATUS = ['ABERTA', 'FECHADA'];

    public const OPPORTUNITY_APPLICATION_STATUS = ['PENDENTE', 'ACEITO', 'RECUSADO'];

    public const REPORT_STATUS = ['PENDENTE', 'ANALISADO', 'RESOLVIDO'];
}
