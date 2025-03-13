<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/x-icon">
    <title>E-School Zambia Contact</title>
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
    <section class="contact-us" aria-labelledby="contact-heading">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-item">
                    <div class="contact-details">
                        <h3><i class="fa fa-envelope-o" aria-hidden="true"></i>For General Inquiries</h3>
                        <p><a href="mailto:eschool240@gmail.com">eschool240@gmail.com</a></p>
                    </div>
                </div>

                <div class="contact-item">

                    <div class="contact-details">
                        <h3><i class="fa fa-phone" aria-hidden="true"></i>Phone</h3>
                        <p>Call us at: <a href="tel:+260765574796">+260 765 574 796</a></p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-details">
                        <h3><i class="fa fa-clock-o" aria-hidden="true"></i>Office Hours</h3>
                        <p>Monday to Friday: 8:00 AM – 5:00 PM (GMT+2)</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-details">
                        <h3><i class="fa fa-map-marker" aria-hidden="true"></i>Address</h3>
                        <p>Kapini Technologies<br>Chamba Valley, 532A/G6/2/E, Zambia</p>
                        <p><strong>Registration Number:</strong> 320230095865</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-details">
                        <h3><i class="fa fa-users" aria-hidden="true"></i>Follow Us</h3>
                        <p>Stay connected with us on social media:</p>
                        <ul class="social-links">
                            <li><a href="https://www.facebook.com/share/1B6bdQEZnK/?mibextid=wwXIfr" target="_blank"><i class="fa fa-facebook" aria-hidden="true"></i>E-school Zambia</a></li>
                            <li><a href="https://www.instagram.com/eschool_zambia?igsh=aDQ1dHdxbXFxZnQz" target="_blank"><i class="fa fa-instagram" aria-hidden="true"></i> E-school Zambia</a></li>
                            <li><a href="https://x.com/eschool_zambia/status/1805381549531316336?s=48" target="_blank"><i class="fa fa-twitter" aria-hidden="true"></i> E-school Zambia</a></li>
                            <a href="https://whatsapp.com/channel/0029Vaa3lpe72WTw2KxVAv3w" target="_blank" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i>E-school Zambia</a>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="contact-footer">
        <p class="footer-text">We look forward to hearing from you!</p>
    </footer>

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
