<?php

/* base.html */
class __TwigTemplate_6be98153e7175c1ea751ccdc21d01f710877d0aba0e142242712d2f56d4b831f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        ob_start();
        // line 2
        echo "<!doctype html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <title>";
        // line 6
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
    ";
        // line 7
        $this->displayBlock('head', $context, $blocks);
        // line 9
        echo "</head>
<body>
    ";
        // line 11
        $this->displayBlock('body', $context, $blocks);
        // line 14
        echo "</body>
</html>
";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    // line 6
    public function block_title($context, array $blocks = array())
    {
        echo twig_escape_filter($this->env, ($context["title"] ?? null), "html", null, true);
    }

    // line 7
    public function block_head($context, array $blocks = array())
    {
        // line 8
        echo "    ";
    }

    // line 11
    public function block_body($context, array $blocks = array())
    {
        // line 12
        echo "    ";
        echo twig_escape_filter($this->env, ($context["content"] ?? null), "html", null, true);
        echo "
    ";
    }

    public function getTemplateName()
    {
        return "base.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  65 => 12,  62 => 11,  58 => 8,  55 => 7,  49 => 6,  42 => 14,  40 => 11,  36 => 9,  34 => 7,  30 => 6,  24 => 2,  22 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "base.html", "C:\\Users\\Jean\\Documents\\tifod\\code\\tests\\mvp2\\src\\templates\\base.html");
    }
}
