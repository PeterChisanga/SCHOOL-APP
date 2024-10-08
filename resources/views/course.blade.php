<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-school-products</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Nunito:wght@600&family=Poppins:wght@100;200;300;400;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.css"
    />
  </head>

  <body>
    <section class="sub-header">
      <nav>
        <a href="/"><img src="images/logo.jpg" alt="" /></a>
        <div class="nav-links" id="navLinks">
          <i class="fa fa-times" onclick="hideMenu()"></i>
          <ul>
            <li><a href="/">HOME</a></li>
            <li><a href="/about">ABOUT</a></li>
            <li><a href="/products">PRODUCTS</a></li>
            <!-- <li><a href="blog.html">BLOG</a></li> -->
            <li><a href="/contact">CONTACT</a></li>
          </ul>
        </div>
        <i class="fa fa-bars" onclick="showMenu()"></i>
      </nav>
      <h1>Our Products</h1>
    </section>

    <!-- ------ Facilities ------ -->

    <section class="facilities">
      <h3>Our Revision Books</h3>
      <p>We have books that suit pupils in grade 10-12</p>

      <div class="row">
        <div class="facilities-col">
          <img src="images/booklet.jpg" alt="" />
          <h3>Maths Paper 2</h3>
          <p>
            description description descriptionLorem dipisicing elit. aperiam
            libero aliquid doloreerendis reiciendis.
          </p>
        </div>
        <div class="facilities-col">
          <img src="images/booklet-2.jpg" alt="" />
          <h3>Maths Paper 1</h3>
          <p>
            description description description Lorem ipsum dolor sit amet
            consectetur adipisicing elit. aperiam reiciendis.
          </p>
        </div>
        <div class="facilities-col">
          <img src="images/booklet-3.jpg" alt="" />
          <h3>Maths paper 1</h3>
          <p>
            description description description Lorem adipisicing elit. aperiam
            libero aliquid doloreerendis reiciendis.
          </p>
        </div>
        <div class="facilities-col">
          <img src="images/booklet-4.jpg" alt="" />
          <h3>Name of product here</h3>
          <p>
            description description description description descriptionLorem
            ipsum dolor sit amet consectetur adipisicing elit. aperiam libero
            aliquid
          </p>
        </div>
      </div>
    </section>

    <!-- ------------ footer ---------- -->

    <section class="footer">
      <div class="icons">
        <i class="fa fa-facebook"></i>
        <i class="fa fa-twitter"></i>
        <i class="fa fa-instagram"></i>
        <i class="fa fa-linkedin"></i>
      </div>
      <p>Made with <i class="fa fa-heart-o"></i> by PCM</p>
      <p></p>
    </section>

    <!-- -------JavaScript for toggle Menu------ -->
    <script>
      var navLinks = document.getElementById("navLinks");
      function showMenu() {
        navLinks.style.right = "0";
      }
      function hideMenu() {
        navLinks.style.right = "-200px";
      }
    </script>
  </body>
</html>
