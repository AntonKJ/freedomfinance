<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>    <meta charset="UTF-8">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <title>Product Form</title>
</head>
<body>
<br/>
<br/>
<br/>
<br/>
<?php if ($_GET['productlist'] == 1 ){

    include_once('dataproducts.php');

 } else { ?>
<form id="formProduct" action="dataproducts.php">
    <h4>New Item</h4>
    <label>Title:
        <input type="text" name="title" placeholder="Товар текст" />
        <span class="title-error"></span>
    </label>
    <br/>
    <label>Price:
        <input type="text" name="price" placeholder="47.99" />
        <span class="price-error"></span>
    </label>
    <br/>
    <label>Date and time:
        <input type="datetime-local" name="datetime" placeholder="2020.10.10 14:20:55" />
        <span class="datetime-error"></span>
    </label>
    <br/>
    <button type="submit">Add</button>
</form>
<script type="text/javascript">
    $(function () {

        $("#formProduct").submit(function(e) {

            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function(data)
                {
                    data = jQuery.parseJSON( data );

                    if (data.status == 'error') {
                        if (data.errors.title) {
                            $('.title-error').text(data.errors.title);
                        }
                        if (data.errors.price) {
                            $('.price-error').text(data.errors.price);
                        }
                        if (data.errors.datetime) {
                            $('.datetime-error').text(data.errors.datetime);
                        }

                    }

                    if (data.status == 'success'){
                        alert('Товар успешно добавлен в базу!');
                        setTimeout(function () {
                            document.location.reload();
                        }, 100);
                    }
                }
            });

        });
    });
</script>
<style>
    form h4 {
        font-family: Arial;
        font-size: 17px;
        size: 17px;
        margin-top: -20px;
    }
    form {
        padding: 50px 50px 10px;
        border-radius: 5px;
        border: 1px solid #000;
        display: block;
        margin: 0 auto;
        width: 400px;
    }
    label {
        font-family: Arial;
        display: block;
        padding-top: 20px;
    }
    input {
        margin: 5px;
        height:30px;
        border: none;
        border-bottom: 1px solid #000;
        width: 70%;
        margin-top: -12px;
        float: right;
    }
    input:focus {
        border:none;
        outline: 0;
        border-bottom: 1px solid #000;
    }
    button {
        margin-top: 25px;
        display: block;
        margin-left: 260px;
        width: 170px;
        /* remove default behavior */
        appearance:none;
        -webkit-appearance:none;
        /* usual styles */
        padding:10px;
        border:none;
        background-color:#3F51B5;
        color:#fff;
        font-weight:600;
        border-radius:5px;
        *width:100%;
    }
    .title-error, .price-error, .datetime-error{
        position: relative;
        font-size: 10px;
        color: red;
        margin-top: -2px;
        float: right;
        width: 72%;
    }
    .label-text{
        display: block;
        width: 120px;
    }
</style>
<?php } ?>
</body>
</html>