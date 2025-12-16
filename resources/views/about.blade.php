<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('images/logo_school.png') }}" type="image/x-icon">
    <title>E-School Zambia About</title>
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
        <a href="/"><img src="images/logo_school.png" alt="" /></a>
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
          <h1>We provide high-quality software and technology solutions to our clients.</h1>
          <p>
            E-School is a comprehensive platform designed to help school owners efficiently and profitably manage their institutions. With E-School, administrators can easily track income by monitoring student payments and managing expenses with organized records and receipts.
          </p>
          <p>The platform also simplifies student data management, allowing schools to store essential details such as names, grades, parent information, academic performance, and health records—all in one place for easy access and improved efficiency.</p>
        </div>
        <div class="about-col">
          <img src="images/about-final.jpg" alt="" />
        </div>
      </div>
    </section>

    <!-- ------------ footer ---------- -->
    @include('footer')

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
