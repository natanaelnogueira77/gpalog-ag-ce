<?php 

namespace Src\Utils;

class ErrorMessages 
{
    public static function getByCode(int $code): string 
    {
        if($code == 400) {
            return _('A requisição não pôde ser processada devido a algum erro do cliente!');
        } elseif($code == 403) {
            return _('Você não tem permissão de acessar!');
        } elseif($code == 404) {
            return _('A página ou recurso não pôde ser encontrado!');
        } elseif($code == 405) {
            return _('O método utilizado não é permitido para essa URL!');
        } elseif($code == 422) {
            return _('O seu pedido foi recebido pelo servidor, mas não pôde ser processado!');
        } elseif($code == 500) {
            return _('Lamentamos, mas houve algum erro interno de servidor!');
        }

        return _('Lamentamos, mas houve algum erro na requisição!');
    }

    public static function requisition(): string 
    {
        return _('Lamentamos, mas houve algum erro na requisição!');
    }

    public static function form(): string 
    {
        return _('Erros de validação! Verifique os campos.');
    }

    public static function excel(): string 
    {
        return _('Lamentamos, mas o excel não pôde ser gerado!');
    }

    public static function pdf(): string 
    {
        return _('Lamentamos, mas o PDF não pôde ser gerado!');
    }

    public static function csvImport(): string 
    {
        return _('Lamentamos, mas houveram erros na importação do CSV!');
    }
}