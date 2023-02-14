<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" />
    </head>
    <body class="">
        <div class="container mt-5">
            @csrf
            <div class="row g-3">
                <div class="col-md-4 offset-md-3">
                    <input class="form-control form-control-lg" id="form_file" type="file" accept="application/pdf,.doc,.docx">
                </div>
                <div class="col-md-4">
                    <button type="button" id="upload_file" class="btn btn-primary btn-lg">Upload</button>
                </div>
            </div>
        </div>
        <div class="container mt-5">
            <div class="row g-3">
                <div class="col-md-6 offset-md-2">
                    <input class="form-control form-control-lg" id="term" type="text">
                </div>
                <div class="col-md-3">
                    <button type="button" id="search" class="btn btn-primary btn-lg"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </div>
        <div class="container mt-5">
            <div class="row g-3" id="search_results">

            </div>
        </div>        

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary" id="exampleModalLabel">Info:</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
        <script>
            $(function(){
                $("#upload_file").click(function(){
                    var files = $('#form_file')[0].files;
                    var token = $("[name='_token']").val();
                    if(files.length > 0){
                        var fd = new FormData();
                        fd.append('file',files[0]);
                        fd.append('_token', token);
                        $.ajax({
                            url: "{{route('uploadFile')}}",
                            method: 'post',
                            data: fd,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response){
                                $("#exampleModal").modal('toggle');
                                $("#exampleModal .modal-body").html(response.message);
                                $('#form_file').val('');
                            }
                        });
                    }
                });

                $("#search").on('click',function(){
                    var term = $('#term').val();
                    var token = $("[name='_token']").val();
                    $.ajax({
                        url: "{{route('search')}}",
                        method: 'get',
                        data: {'term':term},
                        indexTerm: {'term':term},
                        dataType: 'json',
                        success: function(response){
                                $("#search_results").html('');
                            if (response.data.length) { 
                                $("#search_results").append('<div class="col-md-12"><h5>Search List</h5></div>'); 
                                let term = this.indexTerm.term;                                
                                response.data.forEach(element => {
                                    let content = element.content;
                                    let pos = content.toUpperCase().indexOf(term.toUpperCase());
                                    var output = [element.content.slice(0, pos + term.length), '</span>', element.content.slice(pos + term.length)].join('');
                                    output = [output.slice(0, pos), '<span class="text-danger bg-warning">', output.slice(pos)].join('');
                                    
                                    while(pos > -1) {
                                        pos = output.toUpperCase().indexOf(term.toUpperCase(), pos+term.length+45);

                                        output = [output.slice(0, pos + term.length), '</span>', output.slice(pos + term.length)].join('');
                                        output = [output.slice(0, pos), '<span class="text-danger bg-warning">', output.slice(pos)].join('');
                                    }
                                    $("#search_results").append('<div class="col-md-12 pb-3">'+output+'</div>');
                                });
                            }
                            else {
                                $("#search_results").html('<div class="col-md-12"><h5>'+response.message+'</h5></div>');
                            } 
                        }
                    });
                });
            })
        </script>
    </body>
</html>
