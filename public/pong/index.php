<!DOCTYPE html>
<html lang="en">
<head>
<title>Pong</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
	canvas.width = window.innerWidth;
	canvas.height = window.innerHeight;
	var ctx;

	// game settings
	const tickRate = 60;
	var difficulty = 2;

	// the ball
	const ballDiameter = 15;
	var ballX = canvas.width / 2;
	var ballY = canvas.height / 2;
	var ballSpeedX = 15;
	var ballSpeedY = ballSpeedX / 3;

	// paddles
	var paddle1Y = 250;
	var paddle2Y = 250;
	const paddleHeight = 150;

	// Scoring
	var player1Score = 0;
	var player2Score = 0;
	var roundWinnder = 0;
	var showingStartScreen = true;
	var showingWinScreen = false;
	const winningScore = 5;

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
				paddle1Y = mousePos.y - paddleHeight / 2;
			}
		);

		// Restart game/round
		canvas.addEventListener('mousedown',
			function(evt) {
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
			}
		);

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
		ballSpeedX = (ballSpeedX > 0) ? -15 : 15;
		ballSpeedY = ballSpeedX / 3;
		ballX = canvas.width / 2;
		ballY = canvas.height / 2;
	}

	function computerMovement() {
		var paddle2YCenter = paddle2Y + paddleHeight / 2;
		if (difficulty == 1) {
			if (paddle2YCenter < ballY - 35) {
				paddle2Y += 6;
			} else if (paddle2YCenter > ballY + 35) {
				paddle2Y -= 6;
			}
		} else if (difficulty === 2) {
			if (paddle2YCenter < ballY - 30) {
				paddle2Y += 12;
			} else if (paddle2YCenter > ballY + 30) {
				paddle2Y -= 12;
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
		if (ballX - ballDiameter < 0) {
			if (ballY > paddle1Y && ballY < paddle1Y + paddleHeight) {
				ballSpeedX = -ballSpeedX;

				deltaY = ballY - (paddle1Y + paddleHeight / 2);
				ballSpeedY = deltaY * 0.35;
			} else {
				player2Score++;
				ballReset();
			}
		}
		//right-side ball/wall AND ball/paddle collision
		if (ballX + ballDiameter > canvas.width) {
			if (ballY > paddle2Y && ballY < paddle2Y + paddleHeight) {
				ballSpeedX = -ballSpeedX;

				deltaY = ballY - (paddle2Y + paddleHeight / 2);
				ballSpeedY = deltaY * 0.35;
			} else {
				player1Score++;
				ballReset();
			}
		}
		//top ball/wall collision
		if (ballY - ballDiameter < 0) {
			ballSpeedY = -ballSpeedY;
		}
		//bottom ball/wall collision
		if (ballY + ballDiameter > canvas.height) {
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
				"50px Arial", "center", "middle", "white");

			return;
		} else if (showingStartScreen) {
			document.body.className = '';
			colorTxt("START ROUND", canvas.width / 2, canvas.height / 2,
				"50px Arial", "center", "middle", "white");
			return;
		}
		drawNet();
		// Paddle 1
		colorRect(0, paddle1Y, 10, paddleHeight, 'white');
		// Paddle 1
		colorRect(canvas.width - 10, paddle2Y, 10, paddleHeight, 'white');
		// Draw the ball
		colorCircle(ballX, ballY, ballDiameter, 'red');
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
		colorTxt(player1Score, 10, 50,
			"50px Arial", "left", "alphabetic", "blue");
	}

	function drawPlayer2Score() {
		colorTxt(player2Score, canvas.width - 10, 50,
			"50px Arial", "right", "alphabetic", "red");
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

	function colorTxt(txt, x, y, font, align, baseAlign, drawColor) {
		ctx.font = font;
		ctx.fillStyle = drawColor;
		ctx.textAlign = align;
		ctx.baseAlign = baseAlign;
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