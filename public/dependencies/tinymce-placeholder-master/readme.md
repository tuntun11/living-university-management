Placeholder text plugin for TinyMCE
===================================

This plugin brings HTML5 placeholder attribute functionality for the TinyMCE editor.

Usage
-----

* Add the plugin script to the page
* Add "placeholder" to tinymce config plugins array.
* Add a placeholder attribute to the textarea as usual or set placeholder property in editor settings.

Note: This plugin is not compatible with TinyMCE inline mode. It only works in classic mode.

Installation with bower
-------
To install plugin using bower use command <code>bower install tinymce-placeholder-attribute</code>

Example
-------

Tinymce Plugins Array:
plugins: "fullscreen placeholder"

Textarea:
`<textarea class="tinymce" placeholder="Hello World!"></textarea>`

Styling the placeholder label
--------------------------------

By default, this plugin styles the placeholder with the following attributes:

```js
{
  style: {
    position: 'absolute',
    top:'5px',
    left:0,
    color: '#888',
    padding: '1%',
    width:'98%',
    overflow: 'hidden',
    'white-space': 'pre-wrap'
  }
}
```

You can replace this styling by providing a `placeholder_attrs` section in your TinyMCE config...

```js
tinyMCE.init({
  plugins: 'placeholder',
  placeholder_attrs: // (new value for the above object...)
});
```

Or alternatively, you can override specific properties of the default CSS by providing the `!important` directive along in your CSS property for the label...

```css
.mce-edit-area {
  label {
    color: #A9A9A9 !important; /* Override text color */
    left: 5px !important; /* Override left positioning */
  }
}
```
