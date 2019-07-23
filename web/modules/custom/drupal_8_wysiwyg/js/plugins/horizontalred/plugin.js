/**
 * @fileOverview Horizontal Style plugin.
 */

(function () {
  var horizontalredCmd = {
    canUndo: false, // The undo snapshot will be handled by 'insertElement'.
    exec: function (editor) {
      var hr = editor.document.createElement('hr');
      hr.setAttribute('class', 'horizontal-red');
      editor.insertElement(hr);
    },

    allowedContent: 'hr',
    requiredContent: 'hr'
  };

  var pluginName = 'horizontalred';

  // Register a plugin named "horizontalred".
  CKEDITOR.plugins.add(pluginName, {
    icons: 'horizontalred',
    init: function (editor) {
      var pluginDirectory = this.path;

      if (editor.blockless) {
        return;
      }

      editor.addCommand(pluginName, horizontalredCmd);
      editor.ui.addButton && editor.ui.addButton('horizontalred', {
        label: 'Insert Horizontal Red',
        command: pluginName,
        toolbar: 'insert'
      });
      editor.addContentsCss(pluginDirectory + 'css/horizontalred.css');
    }
  });
})();
