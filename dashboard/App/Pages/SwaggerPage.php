<?php
namespace App\Pages;

use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Swagger UI page that renders the OpenAPI spec.
 */
class SwaggerPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTitle('API Documentation');

        $baseUrl = App::getConfig()->getBaseURL();

        // Swagger UI CSS + JS from CDN
        $this->addCSS('https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css');

        $this->insert(new HTMLNode('div', ['id' => 'swagger-ui']));

        $script = new HTMLNode('script', ['src' => 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js']);
        $this->getDocument()->getBody()->addChild($script);

        $initScript = new HTMLNode('script');
        $initScript->text("
SwaggerUIBundle({
    url: '{$baseUrl}/apis/openapi',
    dom_id: '#swagger-ui',
    presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
    layout: 'BaseLayout'
});
", false);
        $this->getDocument()->getBody()->addChild($initScript);
    }
}
