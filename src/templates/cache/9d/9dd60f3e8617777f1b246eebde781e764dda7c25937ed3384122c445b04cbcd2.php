<?php

/* post/branch.html */
class __TwigTemplate_0a19c53a0ee0106234c3fd96dd6a8d859afcf213f672fc40530d8389308de1b3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $context["children"] = twig_get_attribute($this->env, $this->getSourceContext(), ($context["post"] ?? null), "children", array(), "array");
        // line 2
        echo "<div class=\"post-level";
        if (($context["isActive"] ?? null)) {
            echo " active-level";
        }
        echo "\" id=\"";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), ($context["post"] ?? null), "id", array(), "array"), "html", null, true);
        echo "-children\">
";
        // line 3
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["children"] ?? null));
        $context['loop'] = array(
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        );
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["i"] => $context["child"]) {
            // line 4
            echo "    ";
            $context["lastChild"] = twig_get_attribute($this->env, $this->getSourceContext(), ($context["children"] ?? null), (twig_length_filter($this->env, ($context["children"] ?? null)) - 1), array(), "array");
            echo "        
    <div class=\"post";
            // line 5
            if (($context["i"] == 0)) {
                echo " active-post";
            }
            echo "\" id=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), $context["child"], "id", array(), "array"), "html", null, true);
            echo "\"><p>";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), $context["child"], "content", array(), "array"), "html", null, true);
            echo "</p>
    
    <!-- nav bar -->
    ";
            // line 8
            if ((twig_length_filter($this->env, ($context["children"] ?? null)) > 1)) {
                // line 9
                echo "        <span class=\"link\" data-target=\"";
                if (twig_get_attribute($this->env, $this->getSourceContext(), ($context["children"] ?? null), ($context["i"] - 1), array(), "array")) {
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), ($context["children"] ?? null), ($context["i"] - 1), array(), "array"), "id", array(), "array"), "html", null, true);
                } else {
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), ($context["lastChild"] ?? null), "id", array(), "array"), "html", null, true);
                }
                echo "\">&lt;</span>
        ";
                // line 10
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["children"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["t_child"]) {
                    // line 11
                    echo "            <span class=\"link\" data-target=\"";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), $context["t_child"], "id", array(), "array"), "html", null, true);
                    echo "\">
            ";
                    // line 12
                    if ((twig_get_attribute($this->env, $this->getSourceContext(), $context["t_child"], "id", array(), "array") == twig_get_attribute($this->env, $this->getSourceContext(), $context["child"], "id", array(), "array"))) {
                        // line 13
                        echo "                ";
                        echo twig_escape_filter($this->env, ($context["i"] + 1), "html", null, true);
                        echo "/";
                        echo twig_escape_filter($this->env, twig_length_filter($this->env, ($context["children"] ?? null)), "html", null, true);
                        echo "<br>&nbsp;&nbsp;
            ";
                    }
                    // line 14
                    echo "•</span>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['t_child'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 16
                echo "        <span class=\"link\" data-target=\"
        ";
                // line 17
                if (twig_get_attribute($this->env, $this->getSourceContext(), ($context["children"] ?? null), ($context["i"] + 1), array(), "array")) {
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), ($context["children"] ?? null), ($context["i"] + 1), array(), "array"), "id", array(), "array"), "html", null, true);
                } else {
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), ($context["children"] ?? null), 0, array(), "array"), "id", array(), "array"), "html", null, true);
                }
                echo "\">&gt;</span>
    ";
            }
            // line 19
            echo "    
    <!-- add content -->
    <a href=\"#";
            // line 21
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), $context["child"], "id", array(), "array"), "html", null, true);
            echo "\">url</a><form action=\"/add-post.php\" method=\"post\"><input typ=\"text\" name=\"content\" placeholder=\"Répondre\"><input type=\"hidden\" name=\"parent_id\" value=\"";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), $context["child"], "id", array(), "array"), "html", null, true);
            echo "\"><input type=\"hidden\" name=\"project_id\" value=\"";
            echo twig_escape_filter($this->env, ($context["projectId"] ?? null), "html", null, true);
            echo "\"><button>Envoyer</button></form>
    </div>
    
    <!-- post children -->
    ";
            // line 25
            if (twig_get_attribute($this->env, $this->getSourceContext(), $context["child"], "children", array(), "array")) {
                // line 26
                echo "        ";
                $this->loadTemplate("post/branch.html", "post/branch.html", 26)->display(array_merge($context, array("post" => $context["child"], "isActive" => ($context["i"] == 0))));
                // line 27
                echo "    ";
            }
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['i'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 29
        echo "</div>";
    }

    public function getTemplateName()
    {
        return "post/branch.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  148 => 29,  133 => 27,  130 => 26,  128 => 25,  117 => 21,  113 => 19,  104 => 17,  101 => 16,  94 => 14,  86 => 13,  84 => 12,  79 => 11,  75 => 10,  66 => 9,  64 => 8,  52 => 5,  47 => 4,  30 => 3,  21 => 2,  19 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "post/branch.html", "C:\\Users\\Jean\\Documents\\tifod\\code\\tests\\mvp2\\src\\templates\\post\\branch.html");
    }
}
