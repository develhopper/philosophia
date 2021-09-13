<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Philosophia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="info">
        <i class="las la-info-circle icon"></i>
        <div class="info-body">
            <h2>Help: </h2>
            <p>type commands in terminal</p>
            <ul>
                <li><span>ls</span> : get list of categories and posts in current category</li>
                <li><span>cd {category}</span> : change current category to {name}</li>
                <li><span>login -u {username}</span> : login using {username}</li>
                <li><span>logout</span> : logout</li>
                <li><span>list roles</span> : list system roles</li>
                <li><span>list permissions</span> : list system permissions</li>
                <li><span>usermod -ar {role} {username}</span> : assign {role} to {username}</li>
                <li><span>usermod -ap {role} {username}</span> : assign {permission} to {username}</li>
                <li><span>usermod -dr {role} {username}</span> : revoke {role} for {username}</li>
                <li><span>usermod -dp {role} {username}</span> : revoke {permission} for {username}</li>
                <li><span>mkdir {category}</span> : create new category</li>
                <li><span>rmdir {category}</span> : remove category</li>
                <li><span>rm {post}</span> : remove post</li>
                <li><span>notepad {post}</span> : create new post or edit if exists</li>
                <li><span>view {post}</span> : view post</li>
                <li><span>logout</span> : logout</li>
            </ul>
        </div>
    </div>
    <div class="terminal col">
        <div class="row titlebar">
            <div class="col-12"><i class="las la-terminal"></i>Terminal</div>
        </div>

        <div class="row" style="height: 100%;">
            <div class="col-12 prompt" tabindex="-1">

            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/29.2.0/classic/ckeditor.js"></script>
    <script src="/js/cookie.js"></script>
    <script src="/js/editor.js"></script>
    <script src="/js/script.js"></script>
</body>
</html>