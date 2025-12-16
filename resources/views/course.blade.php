<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('images/logo_school.png') }}" type="image/x-icon">
    <title>E-School Zambia Products</title>
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
      <h1>Our Products</h1>
    </section>

    <!-- ------ Our Products ------ -->
    <section class="our-products" aria-labelledby="products-heading">
        <div class="container">
            <p>Our E-School Publications are compact, easy-to-use past paper booklets designed to help school pupils with their revision. These booklets contain carefully selected past exam questions to give students a structured way to practice and prepare effectively.</p>
            <p>With a portable format, E-School Publications make it convenient for pupils to study anytime, anywhere. They serve as an excellent tool for reinforcing knowledge, familiarizing students with exam patterns, and boosting confidence before exams.</p>

            <div class="products-grid">
                <div class="product-item">
                    <img src="images/grade-7-copy.jpg" alt="Grade 7 Mathematics Booklet" />
                    <h4>Grade 7 Mathematics</h4>
                </div>
                <div class="product-item">
                    <img src="images/grade-9-copy.jpg" alt="Grade 9 Mathematics 1 Booklet" />
                    <h4>Grade 9 Mathematics Paper 1</h4>
                </div>
                <div class="product-item">
                    <img src="images/grade-7-copy-3.jpg" alt="Grade 7 Mathematics Booklet" />
                    <h4>Grade 7 Mathematics</h4>
                </div>
                <div class="product-item">
                    <img src="images/booklet-4.jpg" alt="Grade 12 Mathematics 1 Booklet" />
                    <h4>Grade 12 Mathematics Paper 1</h4>
                </div>
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
