window.addEventListener('load', function () {
  var agnosticEditorApp = new Vue({
    el: '#agnostic-editor',
    data: {
      editorCode: window.agnosticEditorCode,
      code: window.agnosticEditorCode
    },
    mounted () {
      var vm = this;

      var langTools = ace.require('ace/ext/language_tools');

      var editor = ace.edit('agnostic_code_editor');
      editor.setTheme('ace/theme/monokai');
      editor.getSession().setMode('ace/mode/twig');
      editor.getSession().setTabSize(2);
      editor.setHighlightActiveLine(true);
      editor.session.setUseWrapMode(true);
      editor.setAutoScrollEditorIntoView(true);
      editor.setOptions({
        minLines: 20,
        maxLines: 30,
        fontSize: '18px',
        wrap: true,
        tabSize: 4,
		    enableEmmet: true,
        foldStyle: 'manual',
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true
      });

      editor.getSession().on('change', function(e) {
        vm.code = editor.getSession().getValue();
      });
    }
  });

  var agnosticConditionsApp = new Vue({
    el: '#agnostic-conditions',
    data: {
      placement: window.agnosticConditions.placement,
      method: window.agnosticConditions.method,
      hook: window.agnosticConditions.hook
    }
  });

  var agnosticDataApp = new Vue({
    el: '#agnostic-data',
    data: {
      source: window.agnosticData.source,
      query: window.agnosticData.query
    },
    mounted () {
      var vm = this;

      var editor = ace.edit('agnostic_query_editor');
      editor.setTheme('ace/theme/monokai');
      editor.getSession().setMode('ace/mode/json');
      editor.getSession().setTabSize(2);
      editor.setHighlightActiveLine(true);
      editor.setAutoScrollEditorIntoView(true);
      editor.setOptions({
        maxLines: Infinity,
        fontSize: '18px'
      });

      editor.getSession().on('change', function(e) {
        vm.query = editor.getSession().getValue();
      });
    }
  });
});