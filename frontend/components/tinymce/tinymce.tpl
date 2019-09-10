{component_define_params params=[ 'name', 'value', 'id', 'rows', 'mods', 'classes', 'attributes' ]}

{component 'form.textarea'
    id = $id
    value = $value
    attributes =  [ 'data-editor' => "tinymce", 'data-editor-set' => $set ] 
    rows = $rows|default:10
    params = $params}