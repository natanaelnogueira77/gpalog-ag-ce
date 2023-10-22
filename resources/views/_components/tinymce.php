<script>
    $(function () {
        tinymce.init({
            selector:'textarea.tinymce',
            language: <?php echo json_encode($session->getLanguage()[1] == 'es_ES' ? 'es' : $session->getLanguage()[1]) ?>,
            plugins: ['image', 'table'],
            relative_urls : false,
            remove_script_host : false,
            convert_urls : false,
            a11y_advanced_options: true,
            images_file_types: 'jpg,jpeg,png,svg,webp',
            images_upload_handler: function (blobInfo, success, failure, progress) {
                var xhr, formData;

                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', <?php echo json_encode($mlAdd) ?>);

                xhr.upload.onprogress = function (e) {
                    progress(e.loaded / e.total * 100);
                };

                xhr.onload = function() {
                    var json;

                    if(xhr.status === 403) {
                        failure(<?php echo json_encode(_('Erro de HTTP: ')) ?> + xhr.status, { remove: true });
                        return;
                    }

                    if(xhr.status < 200 || xhr.status >= 300) {
                        failure(<?php echo json_encode(_('Erro de HTTP: ')) ?> + xhr.status);
                        return;
                    }

                    json = JSON.parse(xhr.responseText);

                    if(!json || typeof json.filename != 'string') {
                        failure(<?php echo json_encode(_('JSON Inválido: ')) ?> + xhr.responseText);
                        return;
                    }

                    success(<?php echo json_encode($path . '/' . $storeAt . '/') ?> + json.filename);
                };

                xhr.onerror = function () {
                    failure(<?php echo json_encode(_('O upload da imagem falhou! Código: ')) ?> + xhr.status);
                };

                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('root', <?php echo json_encode($storeAt) ?>);

                xhr.send(formData);
            },
            images_upload_base_path: <?php echo json_encode($path . '/' . $storeAt) ?>,
            image_list: function (list_success) {
                const images = [];
                $.ajax({
                    url: <?php echo json_encode($mlLoad) ?>,
                    type: 'get',
                    data: {
                        root: <?php echo json_encode($storeAt) ?>,
                        limit: 1000,
                        page: 1
                    },
                    dataType: 'json',
                    success: function (response) {
                        if(response.files) {
                            for(var i = 0; i < response.files.length; i++) {
                                images.push({
                                    title: response.files[i], 
                                    value: <?php echo json_encode($path . '/' . $storeAt . '/') ?> + response.files[i]
                                });
                            }
                        }

                        list_success(images);
                    }
                });
            },
            style_formats: [
                {
                    title: 'Headers', 
                    items: [
                        {title: 'Header 1', block: 'h1'},
                        {title: 'Header 2', block: 'h2'},
                        {title: 'Header 3', block: 'h3'},
                        {title: 'Header 4', block: 'h4'},
                        {title: 'Header 5', block: 'h5'},
                        {title: 'Header 6', block: 'h6'}
                    ]
                },
                {
                    title: 'Inline', 
                    items: [
                        {title: 'Bold', inline: 'b', icon: 'bold'},
                        {title: 'Italic', inline: 'i', icon: 'italic'},
                        {title: 'Underline', inline: 'span', styles : {textDecoration : 'underline'}, icon: 'underline'},
                        {title: 'Strikethrough', inline: 'span', styles : {textDecoration : 'line-through'}},
                        {title: 'Superscript', inline: 'sup', icon: 'superscript'},
                        {title: 'Subscript', inline: 'sub', icon: 'subscript'},
                        {title: 'Code', inline: 'code'}
                    ]
                },
                {
                    title: 'Blocks', 
                    items: [
                        {title: 'Paragraph', block: 'p'},
                        {title: 'Blockquote', block: 'blockquote'},
                        {title: 'Div', block: 'div'},
                        {title: 'Pre', block: 'pre'}
                    ]
                },
                {
                    title: 'Alinhamento', 
                    items: [
                        {title: 'Left', block: 'div', styles : {textAlign : 'left'}},
                        {title: 'Center', block: 'div', styles : {textAlign : 'center'}},
                        {title: 'Right', block: 'div', styles : {textAlign : 'right'}},
                        {title: 'Justify', block: 'div', styles : {textAlign : 'justify'}}
                    ]
                }
            ]
        });
    });
</script>