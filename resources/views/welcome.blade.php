<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 Not Found</title>
  <style>
    /* Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Arial', sans-serif;
      background-color: #f8f8f8;
      color: #333;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      text-align: center;
      padding: 2rem;
    }

    .container {
      max-width: 600px;
    }

    h1 {
      font-size: 6rem;
      color: #f53003;
      margin-bottom: 1rem;
    }

    p {
      font-size: 1.5rem;
      margin-bottom: 2rem;
    }

    .gif-image {
      max-width: 100%;
      height: auto;
      margin-bottom: 2rem;
    }

    a {
      display: inline-block;
      text-decoration: none;
      color: white;
      background-color: #f53003;
      padding: 0.75rem 1.5rem;
      border-radius: 5px;
      font-weight: bold;
      transition: background 0.3s;
    }

    a:hover {
      background-color: #c12700;
    }

    /* Responsive */
    @media(max-width: 480px) {
      h1 {
        font-size: 4rem;
      }
      p {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>404</h1>
    <p>Oops! Page not found.</p>
    <img src="https://media.giphy.com/media/9Y5BbDSkSTiY8/giphy.gif" alt="Sad GIF" class="gif-image">
    <br>
    <a href="/">Go Home</a>
  </div>
</body>
</html>
