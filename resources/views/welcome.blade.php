<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-school</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}" />
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
    <section class="header">
      <nav>
        <a href="/"><img src="{{ asset('images/logo.jpg') }}" alt="" /></a>
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
      <div class="text-box">
        <h1>Our aim is to digitize education in Africa.</h1>
        <p>
          E-school is a platform interested in enhancing parent and teacher
          co-operation in Africa.
        </p>
        <a href="/login" class="hero-btn">Login</a>
        <a href="/users/create" class="hero-btn">Register Your School</a>
      </div>
    </section>

    <section class="intro">
      <div class="row">
        <div class="intro-col">
          <h1>All in one</h1>
          <p>
            E-School is a platform that enables school owners to manage their
            institutions efficiently and profitably by tracking income and
            expenses and maintaining student records.
          </p>
          <a href="/about" class="hero-btn">Learn more</a>
        </div>
        <div class="intro-col">
          <img src="{{ asset('images/about-final.jpg') }}" alt="" />
        </div>
      </div>
    </section>

    <!-- ------ course ------ -->
    <section class="course">
      <h1>Features of our platform</h1>

      <div class="row">
        <div class="course-col">
          <i class="fa fa-cogs"></i>
          <h3>Administration Section</h3>
          <p>
            Efficiently track income and expenses, manage records, and monitor
            school operations with ease.
          </p>
        </div>
        <div class="course-col">
          <i class="fa fa-graduation-cap"></i>
          <h3>Student Collaboration</h3>
          <p>
            Store and access all student information, including grades, health
            status, and parent details. Track admission inquiries, customize
            admission forms, and verify the payment of school fees.
          </p>
        </div>
      </div>
      <div class="row">
        <div class="course-col">
          <i class="fa fa-book"></i>
          <h3>Teachers Section</h3>
          <p>
            E-School enables teachers to effectively monitor student
            performance, record grades, and track attendance.
          </p>
        </div>
        <div class="course-col">
          <i class="fa fa-user"></i>
          <h3>Parents Section</h3>
          <p>
            Parents can view and follow the progress of their child and can
            easily get in touch with teachers. They can also view results of
            their child.
          </p>
        </div>
      </div>
      <div class="row">
        <div class="course-col">
          <i class="fa fa-calendar"></i>
          <h3>Timetable & Attendance</h3>
          <p>
            Easily mark attendance and create an error-free timetable. Perform
            classroom and subject allocation for teachers.
          </p>
        </div>
        <div class="course-col">
          <i class="fa fa-laptop"></i>
          <h3>Online Classes</h3>
          <p>
            E-School now integrates with various video conferencing software
            such as Google Meet.
          </p>
        </div>
      </div>
    </section>

    <section class="intro">
      <div class="row">
        <div class="intro-col">
          <img src="{{ asset('images/mission.jpg') }}" alt="" />
        </div>
        <div class="intro-col">
          <h1>Our Mission</h1>
          <p>
            E-school is a platform that allows school owners to run their
            schools efficiently and profitably. On this platform the school
            owner can truck their incomes by trucking the payment of each and
            every student at the school. Similarly you are able to truck your
            expenses pupils performance tests exams and salaries of school
            staff.
          </p>
          <a href="/about" class="hero-btn">Learn more</a>
        </div>
      </div>
    </section>

    <section class="testimonials">
      <h1>What Our Clients Say</h1>
      <p>Below is the information about the experience of our clients.</p>

      <div class="row">
        <div class="testimonial-col">
          <img src="{{ asset('images/user2.jpg') }}" alt="" />
          <div>
            <p>
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae
              deserunt commodi nihil optio, ut reprehenderit. Nisi sed possimus,
              hic officia recusandae molestiae praesentium, quaerat magni
              dolorem fuga, in nihil. Hic?
            </p>
            <h3>Mr MWAMBA Peter</h3>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star-o"></i>
          </div>
        </div>
        <div class="testimonial-col">
          <img src="{{ asset('images/user2.jpg') }}" alt="" />
          <div>
            <p>
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae
              deserunt commodi nihil optio, ut reprehenderit. Nisi sed possimus,
              hic officia recusandae molestiae praesentium, quaerat magni
              dolorem fuga, in nihil. Hic?
            </p>
            <h3>Mr KAPINI Paul</h3>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star-half-o"></i>
          </div>
        </div>
      </div>
    </section>

    <section class="footer">
      <div class="icons">
        <i class="fa fa-facebook"></i>
        <i class="fa fa-twitter"></i>
        <i class="fa fa-instagram"></i>
        <i class="fa fa-linkedin"></i>
      </div>
      <p>Made with <i class="fa fa-heart-o"></i> by PCM</p>
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
