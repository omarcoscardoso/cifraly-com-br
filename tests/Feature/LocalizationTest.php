<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    /**
     * Test that application locale and timezone are configured to pt_BR and America/Sao_Paulo.
     */
    public function test_application_locale_and_timezone_are_configured_to_pt_br(): void
    {
        $this->assertSame('pt_BR', config('app.locale'));
        $this->assertSame('pt_BR', app()->getLocale());
        $this->assertSame('America/Sao_Paulo', config('app.timezone'));
    }

    /**
     * Test that standard Portuguese translations are loaded properly.
     */
    public function test_portuguese_translations_are_loaded(): void
    {
        $this->assertSame('Essas credenciais não foram encontradas em nossos registros.', __('auth.failed'));
        $this->assertSame('Próximo &raquo;', __('pagination.next'));
        $this->assertSame('Ação', __('Action'));
        $this->assertSame('O campo nome é obrigatório.', __('validation.required', ['attribute' => 'nome']));
    }
}
