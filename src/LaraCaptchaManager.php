<?php

namespace LaraCaptcha;

use Illuminate\Support\HtmlString;
use ReCaptcha\ReCaptcha;

class LaraCaptchaManager
{
    /**
     * URL for reCAPTCHA siteverify API.
     */
    const CLIENT_API = "https://www.google.com/recaptcha/api.js";

    /**
     * Default hidden field name to set verify token.
     */
    const HIDDEN_FIELD_NAME = 'recaptcha_token';

    /**
     * Default action for reCAPTCHA.
     */
    const DEFAULT_ACTION = "submit";

    /**
     * Default score threshold for reCAPTCHA.
     */
    const DEFAULT_SCORE_THRESHOLD = 0.5;

    /**
     * The reCAPTCHA site key
     * @var string
     */
    private $sitekey;

    /**
     * The reCAPTCHA secret
     * @var string
     */
    private $secret;

    /**
     * The from ID using reCAPTCHA
     * @var string
     */
    private $formId;

    /**
     * The hidden field name
     * @var string
     */
    private $hiddenFieldName;

    /**
     * Create instance with configurations.
     *
     * @param $sitekey
     * @param $secret
     * @param $formId
     * @param $hiddenFieldName
     */
    public function __construct($sitekey, $secret, $formId, $hiddenFieldName)
    {
        $this->sitekey = $sitekey;
        $this->secret = $secret;
        $this->formId = $formId;
        $this->hiddenFieldName = $hiddenFieldName;
    }

    /**
     * Initialize Google reCAPTCHA script tag.
     *
     * @param string|null $lang
     * @param string|null $callback
     *
     * @return HTMLString
     */
    public function initScript($lang = null, $callback = null)
    {
        if (empty($callback)) {
            return new HTMLString($this->buildWidget($lang));
        }

        return new HTMLString($this->buildWidgetWith($lang, $callback));
    }

    /**
     * Render javascript tag to submit the form
     *
     * @param string|null $action
     * @param string|null $callback
     *
     * @return HTMLString
     */
    public function render($action = null, $callback = null)
    {
        if (empty($action)) {
            $action = $this::DEFAULT_ACTION;
        }

        if (empty($callback)) {
            return new HtmlString($this->buildScript($action));
        }

        return new HtmlString($this->buildScriptHandleCallback($action, $callback));
    }

    /**
     * Verify the user request using google/recaptha.
     *
     * @param string $action
     * @param float $scoreThreshold
     * @param string $token
     * @param string $ip
     *
     * @return bool
     */
    public function verify($action, $scoreThreshold, $token, $ip)
    {
        $recaptcha = new ReCaptcha($this->secret);

        $response = $recaptcha
            ->setExpectedAction($action)
            ->setScoreThreshold($scoreThreshold)
            ->verify($token, $ip);

        if ($response->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Build javascript tag with language.
     *
     * @param string|null $lang
     *
     * @return string
     */
    private function buildWidget($lang)
    {
        return '<script src="' . $this::CLIENT_API . '?render=' .  $this->sitekey . '&hl=' . $lang . '" async defer></script>';
    }

    /**
     * Build javascript tag with language and callback
     *
     * @param string|null $lang
     * @param string|null $callback
     *
     * @return string
     */
    private function buildWidgetWith($lang, $callback)
    {
        return '<script src="' . $this::CLIENT_API . '?render=' . $this->sitekey . '&hl=' . $lang . '&callback=' . $callback . '" async defer></script>';
    }

    /**
     * Build javascript tag to submit form with action.
     *
     * @param string $action
     *
     * @return string
     */
    private function buildScript($action)
    {
        return '
            <script type="text/javascript">
                const form = document.getElementById("' .  $this->formId . '");
                form.addEventListener("submit", function(e){
                    e.preventDefault();
                    grecaptcha.ready(function(){
                        grecaptcha.execute("' .  $this->sitekey . '", { action: "' . $action . '" })
                        .then((token) => {
                            document.getElementById("' . $this->hiddenFieldName . '").value = token;
                            form.submit();
                        })
                    });
                });
            </script>
        ';
    }

    /**
     * Build javascript tag to submit form with action and callback.
     *
     * @param string $action
     * @param string callback
     *
     * @return string
     */
    private function buildScriptHandleCallback($action, $callback)
    {
        return '
            <script type="text/javascript">
                const form = document.getElementById("' . $this->formId . '");
                form.addEventListener("submit", function(e){
                    e.preventDefault();
                    grecaptcha.ready(function(){
                        grecaptcha.execute("' . $this->sitekey . '", { action: "' . $action . '" })
                        .then((token) => {
                            document.getElementById("' . $this->hiddenFieldName . '").value = token;
                            ' . $callback . '();
                        })
                    });
                });
            </script>
        ';
    }

    /**
     * Display hidden field for setting recaptcha token.
     *
     * @return string
     */
    public function directive()
    {
        return '<?php echo LaraCaptcha::recaptcha_hidden_field(); ?>';
    }

    /**
     * Render hidden input tag.
     *
     * @return HTMLString
     */
    public function recaptcha_hidden_field()
    {
        return new HtmlString('<input type="hidden" name="' . $this->hiddenFieldName . '" id="' . $this->hiddenFieldName . '" value="">');
    }
}
