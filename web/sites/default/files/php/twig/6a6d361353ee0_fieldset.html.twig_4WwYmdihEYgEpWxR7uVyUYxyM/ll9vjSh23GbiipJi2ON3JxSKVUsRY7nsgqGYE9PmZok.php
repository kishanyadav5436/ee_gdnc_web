<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityNotAllowedTestError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* core/themes/claro/templates/fieldset.html.twig */
class __TwigTemplate_04281d16ff1401e40a8b90f284746290 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 30
        $context["classes"] = ["fieldset", (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 32
($context["attributes"] ?? null), "hasClass", ["fieldgroup"], "method", false, false, true, 32)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fieldset--group") : ("")), "js-form-item", "form-item", "js-form-wrapper", "form-wrapper"];
        // line 40
        $context["wrapper_classes"] = ["fieldset__wrapper", (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 42
($context["attributes"] ?? null), "hasClass", ["fieldgroup"], "method", false, false, true, 42)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fieldset__wrapper--group") : (""))];
        // line 46
        $context["legend_span_classes"] = ["fieldset__label", (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 48
($context["attributes"] ?? null), "hasClass", ["fieldgroup"], "method", false, false, true, 48)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fieldset__label--group") : ("")), (((($tmp =         // line 49
($context["required"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("js-form-required") : ("")), (((($tmp =         // line 50
($context["required"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("form-required") : (""))];
        // line 54
        $context["legend_classes"] = ["fieldset__legend", ((((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 56
($context["attributes"] ?? null), "hasClass", ["fieldgroup"], "method", false, false, true, 56)) && $tmp instanceof Markup ? (string) $tmp : $tmp) &&  !(($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "hasClass", ["form-composite"], "method", false, false, true, 56)) && $tmp instanceof Markup ? (string) $tmp : $tmp))) ? ("fieldset__legend--group") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 57
($context["attributes"] ?? null), "hasClass", ["form-composite"], "method", false, false, true, 57)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fieldset__legend--composite") : ("")), (((        // line 58
($context["title_display"] ?? null) == "invisible")) ? ("fieldset__legend--invisible") : ("fieldset__legend--visible"))];
        // line 62
        $context["description_classes"] = ["fieldset__description"];
        // line 66
        yield "
<fieldset";
        // line 67
        yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 67), "html", null, true);
        yield ">
  ";
        // line 69
        yield "  ";
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["legend"] ?? null), "title", [], "any", false, false, true, 69)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 70
            yield "  <legend";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["legend"] ?? null), "attributes", [], "any", false, false, true, 70), "addClass", [($context["legend_classes"] ?? null)], "method", false, false, true, 70), "html", null, true);
            yield ">
    <span";
            // line 71
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["legend_span"] ?? null), "attributes", [], "any", false, false, true, 71), "addClass", [($context["legend_span_classes"] ?? null)], "method", false, false, true, 71), "html", null, true);
            yield ">";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["legend"] ?? null), "title", [], "any", false, false, true, 71), "html", null, true);
            yield "</span>
  </legend>
  ";
        }
        // line 74
        yield "
  <div";
        // line 75
        yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", [($context["wrapper_classes"] ?? null)], "method", false, false, true, 75), "html", null, true);
        yield ">
    ";
        // line 76
        if (((($context["description_display"] ?? null) == "before") && (($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 76)) && $tmp instanceof Markup ? (string) $tmp : $tmp))) {
            // line 77
            yield "      <div";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["description"] ?? null), "attributes", [], "any", false, false, true, 77), "addClass", [($context["description_classes"] ?? null)], "method", false, false, true, 77), "html", null, true);
            yield ">";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 77), "html", null, true);
            yield "</div>
    ";
        }
        // line 79
        yield "    ";
        if ((($tmp = ($context["inline_items"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 80
            yield "      <div class=\"container-inline\">
    ";
        }
        // line 82
        yield "
    ";
        // line 83
        if ((($tmp = ($context["prefix"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 84
            yield "      <span class=\"fieldset__prefix\">";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["prefix"] ?? null), "html", null, true);
            yield "</span>
    ";
        }
        // line 86
        yield "    ";
        yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["children"] ?? null), "html", null, true);
        yield "
    ";
        // line 87
        if ((($tmp = ($context["suffix"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 88
            yield "      <span class=\"fieldset__suffix\">";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["suffix"] ?? null), "html", null, true);
            yield "</span>
    ";
        }
        // line 90
        yield "    ";
        if ((($tmp = ($context["errors"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 91
            yield "      <div class=\"fieldset__error-message\">
        ";
            // line 92
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["errors"] ?? null), "html", null, true);
            yield "
      </div>
    ";
        }
        // line 95
        yield "    ";
        if ((CoreExtension::inFilter(($context["description_display"] ?? null), ["after", "invisible"]) && (($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 95)) && $tmp instanceof Markup ? (string) $tmp : $tmp))) {
            // line 96
            yield "      <div";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["description"] ?? null), "attributes", [], "any", false, false, true, 96), "addClass", [($context["description_classes"] ?? null)], "method", false, false, true, 96), "html", null, true);
            yield ">";
            yield (string) $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 96), "html", null, true);
            yield "</div>
    ";
        }
        // line 98
        yield "
    ";
        // line 99
        if ((($tmp = ($context["inline_items"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 100
            yield "      </div>
    ";
        }
        // line 102
        yield "  </div>
</fieldset>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["attributes", "required", "title_display", "legend", "legend_span", "content_attributes", "description_display", "description", "inline_items", "prefix", "children", "suffix", "errors"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/themes/claro/templates/fieldset.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  165 => 102,  161 => 100,  159 => 99,  156 => 98,  148 => 96,  145 => 95,  139 => 92,  136 => 91,  133 => 90,  127 => 88,  125 => 87,  120 => 86,  114 => 84,  112 => 83,  109 => 82,  105 => 80,  102 => 79,  94 => 77,  92 => 76,  88 => 75,  85 => 74,  77 => 71,  72 => 70,  69 => 69,  65 => 67,  62 => 66,  60 => 62,  58 => 58,  57 => 57,  56 => 56,  55 => 54,  53 => 50,  52 => 49,  51 => 48,  50 => 46,  48 => 42,  47 => 40,  45 => 32,  44 => 30,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/themes/claro/templates/fieldset.html.twig", "D:\\projects\\ee-gndec-website\\web\\core\\themes\\claro\\templates\\fieldset.html.twig");
    }
    
    public function ensureSecurityChecked(): void
    {
        if ($this->sandbox->isSandboxed($this->source)) {
            $this->checkSecurity();
        }
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 30, "if" => 69];
        static $filters = ["escape" => 67];
        static $functions = [];
        static $tests = [];

        try {
            $this->sandbox->checkSecurity(
                [0 => "set", 1 => "if"],
                [0 => "escape"],
                [],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            } elseif ($e instanceof SecurityNotAllowedTestError && isset($tests[$e->getTestName()])) {
                $e->setTemplateLine($tests[$e->getTestName()]);
            }

            throw $e;
        }

    }
}
