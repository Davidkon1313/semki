<?php get_header(); ?>

<div class="error-404 not-found">
    <style>
        .error-404 {
            background-color: black;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }

        .header-404 {
            font-size: 100px;
            margin-bottom: 20px;
        }

        .btn-404 {
            background-color: green;
            color: white;
            padding: 15px 30px;
            font-size: 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-404:hover {
            background-color: darkgreen;
        }
    </style>

    <h1 class="header-404">404</h1>
    <p>Page Not Found</p>
    <a href="<?php echo get_home_url(); ?>/" class="btn-404">Головна</a>
</div>

<?php get_footer(); ?>