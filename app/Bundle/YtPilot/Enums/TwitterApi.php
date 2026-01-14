<?php

declare(strict_types=1);

namespace  Mediatag\Bundle\YtPilot\Enums;

enum TwitterApi: string
{
    case Syndication = 'syndication';
    case GraphQL = 'graphql';
    case GraphQLLegacy = 'graphql-legacy';
}
