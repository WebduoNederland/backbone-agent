<?php

namespace WebduoNederland\BackboneAgent\Enums;

enum DatabaseDumpStatus: string
{
    case Running = 'running';
    case Finished = 'finished';
    case Failed = 'failed';
}
