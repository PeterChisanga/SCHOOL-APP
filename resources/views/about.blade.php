<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-school-about</title>
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
      <h1>About Us</h1>
    </section>

    <!-- -------------- about us content ---------------- -->

    <section class="about-us">
      <div class="row">
        <div class="about-col">
          <h1>We provide quality software and technologies to our clients</h1>
          <p>
            E-school is a platform that allows school owners to run their
            schools efficiently and profitably. On this platform the school
            owner can truck their incomes by trucking the payment of each and
            every student at the school. Similarly you are able to truck your
            expenses by keeping your records and receipts on the application.
            When it comes to pupils, all the information concerning on a student
            such as name grade parent names grades/marks and health status are
            recoreded easily on the platform.
          </p>
        </div>
        <div class="about-col">
          <img src="images/about-final.jpg" alt="" />
        </div>
      </div>
    </section>

    <!-- -------------- Footer -------------------- -->

    <section class="footer">
      <div class="icons">
        <i class="fa fa-facebook"></i>
        <i class="fa fa-twitter"></i>
        <i class="fa fa-instagram"></i>
        <i class="fa fa-linkedin"></i>
      </div>
      <p>Made by PCM</p>
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
