<?php
namespace App\Pages;

use WebFiori\Framework\Ui\WebPage;

/**
 * Swagger UI page that loads the OpenAPI spec from /apis/openapi.
 */
class SwaggerPage extends WebPage {

    public function __construct() {
        parent::__construct();

        $this->setTitle('API Docs');
        $this->addCss('https://unpkg.com/swagger-ui-dist@5/swagger-ui.css');

        $container = $this->insert('div');
        $container->setID('swagger-ui');

        $this->addJs('https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js');

        $script = $this->insert('script');
        $script->text(
            "SwaggerUIBundle({" .
            "url: '/apis/openapi'," .
            "dom_id: '#swagger-ui'," .
            "responseInterceptor: function(response) {" .
            "if (response.body && response.body.data) { response.body = response.body.data; }" .
            "return response;" .
            "}" .
            "});",
            false
        );
    }
}
