<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-school-contact</title>
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
      <h1>Contact Us</h1>
    </section>

    <!-- ------------ contact us------------ -->

    <!-- <section class="location">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d211989.6870162405!2d-5.67810447571344!3d33.88092574457436!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda044d23bfc49d1%3A0xfbbf80a99e4cde18!2sMeknes!5e0!3m2!1sen!2sma!4v1666726197625!5m2!1sen!2sma"
        width="600"
        height="450"
        style="border: 0"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </section> -->

    <!-- <section class="contact-us">
      <div class="row">
        <div class="contact-col">
          <i class="fa fa-home"></i>
          <span>
            <h5>NR 1182 Marjane 2</h5>
            <p>Meknes,Maroc</p>
          </span>
        </div>
        <div class="contact-col">
          <i class="fa fa-phone"></i>
          <span>
            <h5>+212 696 633 760</h5>
            <p>Monday to Saturday, 10AM to 6PM</p>
          </span>
        </div>
        <div class="contact-col">
          <i class="fa fa-envelope-o"></i>
          <span>
            <h5>pcmholdings@gmail.com</h5>
            <p>Email us your query</p>
          </span>
        </div>
        <div class="contact-col">
          <form action="form-handler.php" method="post">
            <input
              type="text"
              name="name"
              placeholder="Enter your name"
              required
            />
            <input
              type="text"
              name="email"
              placeholder="Enter email address"
              required
            />
            <input
              type="text"
              name="subject"
              placeholder="Enter your subject"
              required
            />
            <textarea
              name="message"
              id=""
              placeholder="Message"
              rows="8"
              required
            ></textarea>
            <button type="submit" class="hero-btn red-btn">Send Message</button>
          </form>
        </div>
      </div>
    </section> -->

    <!-- -------------- Footer -------------------- -->

    <section class="footer">
      <p>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae deserunt
        , ut reprehenderit. Nisi sed possimus,hic officia recusan molestiae
        praesentium, quaerat magni dolorem fuga, in nihil. Hic?
      </p>
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
