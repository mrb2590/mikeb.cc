<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pong - mikeb.cc</title>
<style>
	html, body {
		margin: 0;
		padding: 0;
		width: 100%;
		height: 100%;
		text-align: center;
	}
	canvas {
		width: 100%;
	}
	body.hide-cursor,
	body.hide-cursor * {
		cursor: none;
	}
</style>
</head>
<body>

<canvas id="gameCanvas"></canvas>

<script>
	var canvas = document.getElementById('gameCanvas');
	var ctx;

	// Initial canvas resize
	canvas.width = window.innerWidth;
	canvas.height = window.innerHeight;

	// game settings
	var tickRate = 60;
	var difficulty = 2;

	// text
	var defaultGameTextSize = function() { return canvas.width * 0.05; };

	// the ball
	var defaultBallDiameter = function() { return canvas.width * 0.012; }; //15;
	var defaultBallX = function() { return canvas.width / 2; };
	var defaultBallY = function() { return canvas.height / 2; };
	var defaultBallSpeedX = function() { return canvas.width * 0.01; }; //15;
	var defaultBallSpeedY = function() { return ballSpeedX / 3; };

	var ballDiameter = defaultBallDiameter();
	var ballX = defaultBallX();
	var ballY = defaultBallY();
	var ballSpeedX = defaultBallSpeedX();
	var ballSpeedY = defaultBallSpeedY();

	// paddles
	var defaultPaddleHeight = function() { return canvas.height * 0.18; }; //150;
	var defaultPaddleY = function() { return (canvas.height / 2) - (defaultPaddleHeight() / 2); }; //250;

	var paddle1Y = defaultPaddleY();
	var paddle2Y = defaultPaddleY();

	// Scoring
	var player1Score = 0;
	var player2Score = 0;
	var roundWinnder = 0;
	var showingStartScreen = true;
	var showingWinScreen = false;
	var winningScore = 5;


	function calculateMousePos(evt) {
		var rect = canvas.getBoundingClientRect();
		var root = document.documentElement;
		var mouseX = evt.clientX - rect.left - root.scrollLeft;
		var mouseY = evt.clientY - rect.top - root.scrollTop;
		return {
			x: mouseX,
			y: mouseY
		};
	}

	window.onload = function() {
		disableScroll();
		ctx = canvas.getContext('2d');

		// resize the canvas to fill browser window dynamically
		window.addEventListener('resize', resizeCanvas, false);
		resizeCanvas();

		setInterval(function() {
			moveEverything();
			drawEverything();
		}, 1000 / tickRate);

		// Mouse movement listener
		// and setup touch to mimic mouse movement
		setupTouchControlls();
		canvas.addEventListener('mousemove',
			function(evt) {
				var mousePos = calculateMousePos(evt);
				paddle1Y = mousePos.y - defaultPaddleHeight() / 2;
			}
		);

		// Restart game/round
		canvas.addEventListener('mousedown', function(evt) {
			// restart game
			if (showingWinScreen) {
				document.body.className = 'hide-cursor';
				showingWinScreen = false;
				showingStartScreen = true;
				player1Score = 0;
				player2Score = 0;
			} else if (showingStartScreen) {
				document.body.className = 'hide-cursor';
				showingStartScreen = false;
			}
		});

	};


	/**
	 * Functions
	 */

	function resizeCanvas() {
		canvas.width = window.innerWidth;
		canvas.height = window.innerHeight;

		/**
		 * Your drawings need to be inside this function otherwise they will be reset when 
		 * you resize the browser window and the canvas goes will be cleared.
		 */
		drawEverything(); 
	}
	
	function ballReset() {
		if (player1Score >= winningScore || player2Score >= winningScore) {
			showingWinScreen = true;
		} else {
			showingStartScreen = true;
		}
		// Reverse starting direction for next round
		ballSpeedX = (ballSpeedX > 0) ? -defaultBallSpeedX() : defaultBallSpeedX();
		ballSpeedY = ballSpeedX / 3;
		ballX = defaultBallX();
		ballY = defaultBallY();
	}

	function computerMovement() {
		var paddle2YCenter = paddle2Y + defaultPaddleHeight() / 2;
		if (difficulty == 1) {
			if (paddle2YCenter < ballY - (canvas.height * 0.04)) {
				paddle2Y += canvas.height * 0.013;
			} else if (paddle2YCenter > ballY + (canvas.height * 0.04)) {
				paddle2Y -= canvas.height * 0.013;
			}
		} else if (difficulty === 2) {
			if (paddle2YCenter < ballY - (canvas.height * 0.03)) {
				paddle2Y += canvas.height * 0.018;
			} else if (paddle2YCenter > ballY + (canvas.height * 0.03)) {
				paddle2Y -= canvas.height * 0.018;
			}
		}
	}

	function moveEverything() {
		if (showingWinScreen || showingStartScreen) {
			return;
		}

		var deltaY;

		computerMovement();

		ballX += ballSpeedX;
		ballY += ballSpeedY;

		//left-side ball/wall AND ball/paddle collision
		if (ballX - defaultBallDiameter() / 2 < 0) {
			if (ballY > paddle1Y && ballY < paddle1Y + defaultPaddleHeight()) {
				ballSpeedX = -ballSpeedX;

				deltaY = ballY - (paddle1Y + defaultPaddleHeight() / 2);
				ballSpeedY = deltaY * 0.35;
			} else {
				player2Score++;
				ballReset();
			}
		}
		//right-side ball/wall AND ball/paddle collision
		if (ballX + defaultBallDiameter() / 2 > canvas.width) {
			if (ballY > paddle2Y && ballY < paddle2Y + defaultPaddleHeight()) {
				ballSpeedX = -ballSpeedX;

				deltaY = ballY - (paddle2Y + defaultPaddleHeight() / 2);
				ballSpeedY = deltaY * 0.35;
			} else {
				player1Score++;
				ballReset();
			}
		}
		//top ball/wall collision
		if (ballY - defaultBallDiameter() / 2 < 0) {
			ballSpeedY = -ballSpeedY;
		}
		//bottom ball/wall collision
		if (ballY + defaultBallDiameter() / 2 > canvas.height) {
			ballSpeedY = -ballSpeedY;
		}
	}

	function drawEverything() {
		drawBackground();

		if (showingWinScreen) {
			document.body.className = '';
			var txt;
			if (player1Score >= winningScore) {
				txt = "Left Player Wins";
			} else if (player2Score >= winningScore) {
				txt = "Right Player Wins";
			}
			colorTxt(txt, canvas.width / 2, canvas.height / 2,
				defaultGameTextSize() + "px Arial", "center", "middle", "white");

			return;
		} else if (showingStartScreen) {
			document.body.className = '';
			colorTxt("START ROUND", canvas.width / 2, canvas.height / 2,
				defaultGameTextSize() + "px Arial", "center", "middle", "white");
			return;
		}
		drawNet();
		// Paddle 1
		colorRect(0, paddle1Y, 10, defaultPaddleHeight(), 'white');
		// Paddle 2
		colorRect(canvas.width - 10, paddle2Y, 10, defaultPaddleHeight(), 'white');
		// Draw the ball
		colorCircle(ballX, ballY, defaultBallDiameter(), 'red');
	}

	function drawNet() {
		for (var i = 0; i < canvas.height; i += 40) {
			colorRect(canvas.width / 2 -1, i, 2, 20, 'white');
		}
	}

	function drawBackground() {
		// Draw the background
		colorRect(0, 0, canvas.width, canvas.height, 'black');
		// Draw the score
		drawPlayer1Score();
		// Draw the top score
		drawPlayer2Score();
	}

	function drawPlayer1Score() {
		colorTxt(player1Score, canvas.width * 0.15, canvas.height * 0.25,
			defaultGameTextSize() + "px Arial", "left", "alphabetic", "blue");
	}

	function drawPlayer2Score() {
		colorTxt(player2Score, canvas.width - (canvas.width * 0.15), canvas.height * 0.25,
			defaultGameTextSize() + "px Arial", "right", "alphabetic", "red");
	}

	function colorRect(leftX, topY, width, height, drawColor) {
		ctx.fillStyle = drawColor;
		ctx.fillRect(leftX, topY, width, height);
	}

	function colorCircle(centerX, centerY, radius, drawColor) {
		ctx.fillStyle = drawColor;
		ctx.beginPath();
		ctx.arc(centerX, centerY, radius, 0, Math.PI*2, true);
		ctx.fill();
	}

	function colorTxt(txt, x, y, font, align, baseline, drawColor) {
		ctx.font = font;
		ctx.fillStyle = drawColor;
		ctx.textAlign = align;
		ctx.textBaseline = baseline;
		ctx.fillText(txt, x, y);
	}

	function setupTouchControlls() {
		// Set up touch events for mobile, etc
		canvas.addEventListener("touchstart", function (e) {
						mousePos = getTouchPos(canvas, e);
			var touch = e.touches[0];
			var mouseEvent = new MouseEvent("mousedown", {
				clientX: touch.clientX,
				clientY: touch.clientY
			});
			canvas.dispatchEvent(mouseEvent);
		}, false);
		canvas.addEventListener("touchend", function (e) {
			var mouseEvent = new MouseEvent("mouseup", {});
			canvas.dispatchEvent(mouseEvent);
		}, false);
		canvas.addEventListener("touchmove", function (e) {
			var touch = e.touches[0];
			var mouseEvent = new MouseEvent("mousemove", {
				clientX: touch.clientX,
				clientY: touch.clientY
			});
			canvas.dispatchEvent(mouseEvent);
		}, false);

		// Get the position of a touch relative to the canvas
		function getTouchPos(canvasDom, touchEvent) {
			var rect = canvasDom.getBoundingClientRect();
			return {
				x: touchEvent.touches[0].clientX - rect.left,
				y: touchEvent.touches[0].clientY - rect.top
			};
		}
	}

	// left: 37, up: 38, right: 39, down: 40,
	// spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
	var keys = {37: 1, 38: 1, 39: 1, 40: 1};

	function preventDefault(e) {
		e = e || window.event;
		if (e.preventDefault)
				e.preventDefault();
		e.returnValue = false;  
	}

	function preventDefaultForScrollKeys(e) {
	    if (keys[e.keyCode]) {
	        preventDefault(e);
	        return false;
	    }
	}

	function disableScroll() {
		if (window.addEventListener) // older FF
			window.addEventListener('DOMMouseScroll', preventDefault, false);
		window.onwheel = preventDefault; // modern standard
		window.onmousewheel = document.onmousewheel = preventDefault; // older browsers, IE
		window.ontouchmove  = preventDefault; // mobile
		document.onkeydown  = preventDefaultForScrollKeys;
	}

	function enableScroll() {
		if (window.removeEventListener)
			window.removeEventListener('DOMMouseScroll', preventDefault, false);
		window.onmousewheel = document.onmousewheel = null; 
		window.onwheel = null; 
		window.ontouchmove = null;  
		document.onkeydown = null;  
	}
</script>
</body>
</html>
