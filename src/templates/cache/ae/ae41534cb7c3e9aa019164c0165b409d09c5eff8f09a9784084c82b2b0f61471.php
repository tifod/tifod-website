<?php

/* project-player.html */
class __TwigTemplate_29b840c0f214531a3bffba16f5341b5df8af217b86f8d119646d85be715bacf3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html", "project-player.html", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = array())
    {
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), ($context["project"] ?? null), 0, array(), "array"), "content", array(), "array"), "html", null, true);
    }

    // line 5
    public function block_head($context, array $blocks = array())
    {
        // line 6
        echo "<style>
.link {
    cursor: pointer;
    padding: 5px;
    margin: 5px;
    display: inline-block;
    border-radius: 5px;
    border: none;
    background-color: #ddd;
}
.link:hover { background-color: #bbb; }
.post-level:not(.active-level) { display: none; }
.post:not(.active-post) { display: none; }
.post {
    margin: 5px 0;
    border-radius: 5px;
    padding: 5px;
    border: solid 1px black;
}
#project-player { transition: height 1s; }
</style>
<link rel=\"stylesheet\" href=\"/treant/Treant.css\" type=\"text/css\"/>
<script src=\"/treant/vendor/raphael.js\"></script>
<script src=\"/treant/Treant.js\"></script>
";
    }

    // line 32
    public function block_body($context, array $blocks = array())
    {
        // line 33
        echo "<h1 id=\"project-title\">
";
        // line 34
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), ($context["project"] ?? null), 0, array(), "array"), "content", array(), "array"), "html", null, true);
        echo "
</h1>
<a href=\"/\">Revenir à la liste des projets</a>
<a href=\"#tree-view\">Voir l'arborescence du projet</a>
<div id=\"project-player\">
";
        // line 39
        $this->loadTemplate("post/branch.html", "project-player.html", 39)->display(array_merge($context, array("children" => ($context["children"] ?? null), "isActive" => true)));
        // line 40
        echo "</div>
<a id=\"tree-view\" href=\"#project-title\">Revenir en haut</a>
<div id=\"project-tree\"></div>
<script>
var simple_chart_config = {
chart: { container: \"#project-tree\" },
nodeStructure: 

";
        // line 48
        echo json_encode(twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), twig_get_attribute($this->env, $this->getSourceContext(), ($context["project_json"] ?? null), 0, array(), "array"), "children", array(), "array"), 0, array(), "array"));
        echo "

};
new Treant(simple_chart_config);
document.getElementById('project-player').style.height = document.getElementById('project-player').firstElementChild.offsetHeight + 'px';
var links = document.getElementsByClassName('link');
for(var z = 0; z < links.length; z++) {
    var elem = links[z];
    elem.onclick = function() {
        var allSiblings = this.parentNode.parentNode.childNodes;
        var posts = [];
        for (var i = 0; i < allSiblings.length; i++) {
            if (hasClass(allSiblings[i], 'post')) {
                posts.push(allSiblings[i]);
            }
        }
        for(var y = 0; y < posts.length; y++) {
            var post = posts[y];
            post.className = 'post';
        }
        document.getElementById(this.getAttribute('data-target')).className = 'post active-post';
        
        var allSiblingsLvl = this.parentNode.parentNode.childNodes;
        var levels = [];
        for (var i = 0; i < allSiblingsLvl.length; i++) {
            if (hasClass(allSiblingsLvl[i], 'post-level')) {
                levels.push(allSiblingsLvl[i]);
            }
        }
        for(var y = 0; y < levels.length; y++) {
            var level = levels[y];
            level.className = 'post-level';
        }
        if (document.getElementById(this.getAttribute('data-target') + '-children') != null) {
            document.getElementById(this.getAttribute('data-target') + '-children').className = 'post-level active-level';
        }
        
        document.getElementById('project-player').style.height = document.getElementById('project-player').firstElementChild.offsetHeight + 'px';
    };
}
function hasClass(element, cls) {
    return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
}
</script>
";
    }

    public function getTemplateName()
    {
        return "project-player.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  93 => 48,  83 => 40,  81 => 39,  73 => 34,  70 => 33,  67 => 32,  39 => 6,  36 => 5,  30 => 3,  11 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "project-player.html", "C:\\Users\\Jean\\Documents\\tifod\\code\\tests\\mvp2\\src\\templates\\project-player.html");
    }
}
