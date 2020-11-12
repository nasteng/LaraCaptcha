<?php

namespace LaraCap\Tests\Unit;

use Illuminate\Support\HtmlString;
use LaraCaptcha\LaraCaptchaManager;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class LaraCaptchaManagerTest extends OrchestraTestCase
{
    const SITE_KEY = 'sitekey';
    const SECRET   = 'secret';
    const FORM_ID   = 'form-id';
    const HIDDEN_FIELD_NAME   = 'recaptcha_token';

    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new LaraCaptchaManager(
            $this::SITE_KEY,
            $this::SECRET,
            $this::FORM_ID,
            $this::HIDDEN_FIELD_NAME
        );
    }

    public function testInitScript()
    {
        $simple = new HtmlString('<script src="https://www.google.com/recaptcha/api.js?render=' . $this::SITE_KEY . '&hl=" async defer></script>');
        $withLang = new HtmlString('<script src="https://www.google.com/recaptcha/api.js?render=' . $this::SITE_KEY . '&hl=en" async defer></script>');
        $withCallback = new HtmlString('<script src="https://www.google.com/recaptcha/api.js?render=' . $this::SITE_KEY . '&hl=&callback=doSomething" async defer></script>');
        $withLangAndCallback = new HtmlString('<script src="https://www.google.com/recaptcha/api.js?render=' . $this::SITE_KEY . '&hl=en&callback=doSomething" async defer></script>');

        $this->assertEquals($simple, $this->manager->initScript());
        $this->assertEquals($withLang, $this->manager->initScript('en'));
        $this->assertEquals($withCallback, $this->manager->initScript(null, 'doSomething'));
        $this->assertEquals($withLangAndCallback, $this->manager->initScript('en', 'doSomething'));
    }

    public function testRenderWithActionAndCallback()
    {
        $builtScript = $this->manager->render("test", "doSomething");

        $this->assertEquals(1, preg_match('/' . $this::FORM_ID . '/', $builtScript));
        $this->assertEquals(1, preg_match('/' . $this::SITE_KEY . '/', $builtScript));
        $this->assertEquals(1, preg_match('/\{ action: "test" \}/', $builtScript));
        $this->assertEquals(1, preg_match('/doSomething\(\);/', $builtScript));
    }

    public function testRenderWithAction()
    {
        $builtScript = $this->manager->render('test');

        $this->assertEquals(1, preg_match('/' . $this::FORM_ID . '/', $builtScript));
        $this->assertEquals(1, preg_match('/' . $this::SITE_KEY . '/', $builtScript));
        $this->assertEquals(1, preg_match('/\{ action: "test" \}/', $builtScript));
        $this->assertEquals(1, preg_match('/form.submit\(\);/', $builtScript));
    }

    public function testRenderWithCallback()
    {
        $builtScript = $this->manager->render(null, 'doSomething');

        $this->assertEquals(1, preg_match('/' . $this::FORM_ID . '/', $builtScript));
        $this->assertEquals(1, preg_match('/' . $this::SITE_KEY . '/', $builtScript));
        $this->assertEquals(1, preg_match('/\{ action: "submit" \}/', $builtScript));
        $this->assertEquals(1, preg_match('/doSomething\(\);/', $builtScript));
    }

    public function testRecaptchaHiddenField()
    {
        $field = $this->manager->recaptcha_hidden_field();

        $this->assertEquals(1, preg_match('/' . $this::HIDDEN_FIELD_NAME . '/', $field));
    }
}
