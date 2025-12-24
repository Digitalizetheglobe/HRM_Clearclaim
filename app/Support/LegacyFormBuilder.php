<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Minimal LaravelCollective FormBuilder-compatible API.
 *
 * This is intentionally small: it supports the methods observed in this codebase
 * so we can remove the abandoned laravelcollective/html dependency without
 * rewriting hundreds of Blade templates.
 */
class LegacyFormBuilder
{
    /** @var mixed */
    protected $model = null;

    /** @var bool */
    protected bool $modelBound = false;

    public function model($model, array $options = []): HtmlString
    {
        $this->model = $model;
        $this->modelBound = true;

        return $this->open($options);
    }

    public function open(array $options = []): HtmlString
    {
        $method = strtoupper((string) ($options['method'] ?? 'POST'));
        $spoofedMethod = null;

        if (!in_array($method, ['GET', 'POST'], true)) {
            $spoofedMethod = $method;
            $method = 'POST';
        }

        $attrs = $this->extractFormAttributes($options);
        $attrs['method'] = strtolower($method);
        $attrs['action'] = $this->resolveFormAction($options);

        if (($options['files'] ?? false) === true && empty($attrs['enctype'])) {
            $attrs['enctype'] = 'multipart/form-data';
        }

        $html = '<form'.$this->attributes($attrs).'>';

        // CSRF for non-GET requests
        if (strtoupper($method) !== 'GET') {
            $html .= csrf_field();
        }

        // Method spoofing for PUT/PATCH/DELETE
        if ($spoofedMethod) {
            $html .= method_field($spoofedMethod);
        }

        return new HtmlString($html);
    }

    public function close(): HtmlString
    {
        // reset model binding to avoid leaking across forms
        $this->model = null;
        $this->modelBound = false;

        return new HtmlString('</form>');
    }

    public function label($name, $value = null, array $options = []): HtmlString
    {
        $for = $options['for'] ?? ($options['id'] ?? $name);
        $attrs = array_merge(['for' => $for], $options);
        unset($attrs['for']);

        $text = $value ?? $name;

        return new HtmlString('<label'.$this->attributes($attrs).'>'.$this->escape($text).'</label>');
    }

    public function text($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('text', $name, $value, $options);
    }

    public function email($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('email', $name, $value, $options);
    }

    public function password($name, $value = null, array $options = []): HtmlString
    {
        // In many templates password() is called with (name, options)
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        // Never prefill password fields from model/old input unless explicitly provided
        if ($value === null) {
            $value = '';
        }
        return $this->input('password', $name, $value, $options, false);
    }

    public function number($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('number', $name, $value, $options);
    }

    public function hidden($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('hidden', $name, $value, $options);
    }

    public function date($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('date', $name, $value, $options);
    }

    public function month($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('month', $name, $value, $options);
    }

    public function time($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);
        return $this->input('time', $name, $value, $options);
    }

    public function textarea($name, $value = null, array $options = []): HtmlString
    {
        [$value, $options] = $this->normalizeValueAndOptions($value, $options);

        $attrs = array_merge($options, [
            'name' => $name,
            'id' => $options['id'] ?? $name,
        ]);

        $resolved = $this->resolveValue($name, $value, true);

        return new HtmlString('<textarea'.$this->attributes($attrs).'>'.$this->escape($resolved).'</textarea>');
    }

    public function select($name, $list = [], $selected = null, array $options = []): HtmlString
    {
        $attrs = array_merge($options, [
            'name' => $name,
            'id' => $options['id'] ?? $name,
        ]);

        $multiple = array_key_exists('multiple', $attrs);
        $resolvedSelected = $this->resolveValue($name, $selected, true);
        $selectedValues = $multiple ? Arr::wrap($resolvedSelected) : [$resolvedSelected];

        $placeholder = $attrs['placeholder'] ?? null;
        unset($attrs['placeholder']);

        $html = '<select'.$this->attributes($attrs).'>';

        if ($placeholder !== null && !$multiple) {
            $html .= '<option value="">'.$this->escape($placeholder).'</option>';
        }

        foreach ((array) $list as $value => $label) {
            $isSelected = in_array((string) $value, array_map(fn ($v) => (string) $v, $selectedValues), true);
            $html .= '<option value="'.$this->escapeAttribute($value).'"'.($isSelected ? ' selected' : '').'>'
                .$this->escape($label)
                .'</option>';
        }

        $html .= '</select>';

        return new HtmlString($html);
    }

    public function checkbox($name, $value = 1, $checked = null, array $options = []): HtmlString
    {
        // In this codebase checked is sometimes the string 'checked'
        $isChecked = $checked === true || $checked === 1 || $checked === '1' || $checked === 'checked';

        $attrs = array_merge($options, [
            'type' => 'checkbox',
            'name' => $name,
            'value' => $value,
            'id' => $options['id'] ?? $name,
        ]);

        if ($isChecked) {
            $attrs['checked'] = 'checked';
        }

        return new HtmlString('<input'.$this->attributes($attrs).'/>');
    }

    public function radio($name, $value = null, $checked = false, array $options = []): HtmlString
    {
        $isChecked = (bool) $checked;

        $attrs = array_merge($options, [
            'type' => 'radio',
            'name' => $name,
            'value' => $value,
            'id' => $options['id'] ?? ($name.'_'.Str::slug((string) $value, '_')),
        ]);

        if ($isChecked) {
            $attrs['checked'] = 'checked';
        }

        return new HtmlString('<input'.$this->attributes($attrs).'/>');
    }

    public function submit($value = 'Submit', array $options = []): HtmlString
    {
        $attrs = array_merge($options, [
            'type' => 'submit',
            'value' => $value,
        ]);

        return new HtmlString('<input'.$this->attributes($attrs).'/>');
    }

    // -------------------------
    // Internals
    // -------------------------

    protected function input(string $type, $name, $value = null, array $options = [], bool $useModelAndOld = true): HtmlString
    {
        $attrs = array_merge($options, [
            'type' => $type,
            'name' => $name,
            'id' => $options['id'] ?? $name,
        ]);

        if ($useModelAndOld) {
            $resolved = $this->resolveValue($name, $value, true);
        } else {
            $resolved = $value;
        }

        if ($resolved !== null) {
            $attrs['value'] = $resolved;
        }

        return new HtmlString('<input'.$this->attributes($attrs).'/>');
    }

    protected function resolveValue($name, $explicitValue, bool $useOldAndModel)
    {
        if ($explicitValue !== null) {
            return $explicitValue;
        }

        if ($useOldAndModel && is_string($name) && $name !== '') {
            $old = old($name);
            if ($old !== null) {
                return $old;
            }
        }

        if ($useOldAndModel && $this->modelBound && is_string($name) && $name !== '' && $this->model !== null) {
            if (is_array($this->model) || $this->model instanceof \ArrayAccess) {
                return $this->model[$name] ?? null;
            }

            return data_get($this->model, $name);
        }

        return null;
    }

    protected function normalizeValueAndOptions($value, array $options): array
    {
        // Support calls like: Form::password('password', ['class' => '...'])
        if (is_array($value) && empty($options)) {
            return [null, $value];
        }

        return [$value, $options];
    }

    protected function resolveFormAction(array $options): string
    {
        if (isset($options['url'])) {
            $url = $options['url'];
            if (is_string($url)) {
                return $url;
            }
        }

        if (isset($options['route'])) {
            $route = $options['route'];
            if (is_array($route) && isset($route[0])) {
                $name = $route[0];
                $params = array_slice($route, 1);
                return route($name, $params);
            }
            if (is_string($route)) {
                return route($route);
            }
        }

        if (isset($options['action'])) {
            // action can be controller action string
            return action($options['action']);
        }

        return url()->current();
    }

    protected function extractFormAttributes(array $options): array
    {
        $attrs = $options;
        unset($attrs['url'], $attrs['route'], $attrs['action'], $attrs['method'], $attrs['files']);
        return $attrs;
    }

    protected function attributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            // Support boolean attributes passed as '' or true or 'required'
            if ($value === true || $value === '' || $value === $key) {
                $html .= ' '.$this->escapeAttribute($key);
                continue;
            }

            $html .= ' '.$this->escapeAttribute($key).'="'.$this->escapeAttribute($value).'"';
        }

        return $html;
    }

    protected function escape($value): string
    {
        return e((string) $value);
    }

    protected function escapeAttribute($value): string
    {
        return e((string) $value);
    }
}


