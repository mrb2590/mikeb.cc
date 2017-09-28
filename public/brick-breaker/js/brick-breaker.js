// Canvas
var canvas = document.getElementById('gameCanvas');
var ctx;

// Game data display
var fpsData = document.getElementById('current-fps');

// Game loop data
var framerate = 60;
var pause = false;
var frameCount = 0;	// Store the total frames rendered in a second
var fpsInterval;	// miliseconds between each frame
var startTime;
var now;
var then;
var elapsed;

// Player's paddle
var paddleWidth = 200;
var paddleHeight = 25;
var paddleX = (canvas.width/2)-(paddleWidth/2);
var paddleY = canvas.height - paddleHeight;

// Ball
var ballR = 14;
var ballX = canvas.width / 2;
var ballY = (canvas.height - paddleHeight) - ballR;
var ballSpeedX = 15;
var ballSpeedY = -ballSpeedX / 3;

// Image locations
var lavaImg = new Image();
lavaImg.src = "/brick-breaker/img/lava.webp";
var lavaCounter = 0;
var lavaSpriteTick = 5;
var lavaSpriteWidth = 200;
var lavaSpriteHeight = 20;



// Load and start
window.onload = function() {
	ctx = canvas.getContext('2d');
	bindEventListeners();
	startGameLoop();
};


// Start the game loop
function startGameLoop() {
    fpsInterval = 1000 / framerate;
    then = window.performance.now();
    startTime = then;
    gameLoop();
}

// The main loop
function gameLoop(newtime) {
	// Check if the game has been paused
    if (pause) {
        return;
    }

    // request another frame
    requestAnimationFrame(gameLoop);

    // calc elapsed time since last loop
    now = newtime;
    elapsed = now - then;

	update();

    // if enough time has elapsed, draw the next frame
    if (elapsed > fpsInterval) {
        // Get ready for next frame by setting then=now, but...
        // Also, adjust for fpsInterval not being multiple of 16.67
        then = now - (elapsed % fpsInterval);
        // draw the frame
		drawFrame();

        // TESTING...Report #seconds since start and achieved fps.
        var sinceStart = now - startTime;
        var currentFps = Math.round(1000 / (sinceStart / ++frameCount) * 100) / 100;
        fpsData.innerHTML = currentFps;

    }
}

// Draw a frame
function drawFrame() {
	drawBackground();
	drawMapBricks(0);
	drawBall();
	drawLava();
	drawPlayersPaddle();
}

// Update all movement
function update() {
	checkPaddleWallCollision();
	CheckBrickBallCollision(0);
	moveBall();
}



/**
 * Event listeners
 */

function bindEventListeners() {
	// Have the paddle follow the mouse
	canvas.addEventListener('mousemove', function(evt) {
			var mousePos = calculateMousePos(evt);
			paddleX = mousePos.x - paddleWidth / 2;
		}
	);
}



/**
 * Movement Functions
 */

// Calculate the mouse x and y position
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

// Check paddle for wall collisions and set next to wall if so
function checkPaddleWallCollision() {
	if (paddleX < 0) {
		paddleX = 0;
	} else if (paddleX > canvas.width - paddleWidth) {
		paddleX = canvas.width - paddleWidth;
	}
}

// Check paddle for wall collisions and set next to wall if so
function moveBall() {
	var deltaX; // The change in the ball's X direction

	// Move the ball ahead
	ballX += ballSpeedX;
	ballY += ballSpeedY;

	// Left-side ball/wall collision
	if (ballX - ballR < 0) {
		ballSpeedX = -ballSpeedX;
	}
	// Right-side ball/wall collision
	if (ballX + ballR > canvas.width) {
		ballSpeedX = -ballSpeedX;
	}
	// Top ball/wall collision
	if (ballY - ballR < 0) {
		ballSpeedY = -ballSpeedY;
	}
	// Bottom ball/lava collision
	if (ballY + ballR > canvas.height - lavaSpriteHeight) {
		resetBall();
	}
	// Ball/paddle collision
	if (ballY + ballR == canvas.height - paddleHeight &&
		ballX >= paddleX && ballX <= paddleX + paddleWidth) {
		ballSpeedY = -ballSpeedY;
		// Change ball direction and speed based
		// on where on the paddle it was hit
		deltaX = ballX - (paddleX + (paddleWidth / 2));
		ballSpeedX = deltaX * 0.35;
	}
}

// Reset the ball to the original position
function resetBall() {
	ballX = canvas.width / 2;
	ballY = (canvas.height - paddleHeight) - ballR;
	ballSpeedX = 15;
	ballSpeedY = -ballSpeedX / 3;
}

// Check for brick/ball collision, if so add 1 to hit and change color,
// if the brick has 3 hits, remove it
// if there are no bricks left, end map
function CheckBrickBallCollision(currentMap) {
	// Check if ball is colliding with each brick
	for (var i = 0; i < maps[currentMap].bricks.length; i++) {
		// console.log(maps[currentMap].bricks[i]);
		// Check if brick still exists, skip if not
		if (maps[currentMap].bricks[i].hits >= maps[currentMap].brickColors.length) {
			continue;
		}
		// Right-side of brick collision
		if (ballX - ballR < maps[currentMap].bricks[i].x + maps[currentMap].brickWidth &&
			ballX + ballR > maps[currentMap].bricks[i].x + maps[currentMap].brickWidth &&
			ballY >= maps[currentMap].bricks[i].y &&
			ballY <= maps[currentMap].bricks[i].y + maps[currentMap].brickHeight) {
			maps[currentMap].bricks[i].hits++;
			ballSpeedX = -ballSpeedX;
		}
		// Left-side of brick collision
		else if (ballX + ballR > maps[currentMap].bricks[i].x &&
			ballX - ballR < maps[currentMap].bricks[i].x &&
			ballY >= maps[currentMap].bricks[i].y &&
			ballY <= maps[currentMap].bricks[i].y + maps[currentMap].brickHeight) {
			maps[currentMap].bricks[i].hits++;
			ballSpeedX = -ballSpeedX;
		}
		// Top of brick collision
		else if (ballY + ballR > maps[currentMap].bricks[i].y &&
			ballY - ballR < maps[currentMap].bricks[i].y &&
			ballX >= maps[currentMap].bricks[i].x &&
			ballX <= maps[currentMap].bricks[i].x + maps[currentMap].brickWidth) {
			maps[currentMap].bricks[i].hits++;
			ballSpeedY = -ballSpeedY;
		}
		// Bottom of brick collision
		else if (ballY - ballR < maps[currentMap].bricks[i].y + maps[currentMap].brickHeight &&
			ballY + ballR > maps[currentMap].bricks[i].y + maps[currentMap].brickHeight &&
			ballX >= maps[currentMap].bricks[i].x &&
			ballX <= maps[currentMap].bricks[i].x + maps[currentMap].brickWidth) {
			maps[currentMap].bricks[i].hits++;
			ballSpeedY = -ballSpeedY;
		}
	}
}



/**
 * Game component draw functions
 */

// Draw background
function drawBackground() {
	// Draw the background
	colorRect(0, 0, canvas.width, canvas.height, '#333');
}

// Draw the player's paddle
function drawPlayersPaddle() {
	colorRect(paddleX, paddleY, paddleWidth, paddleHeight, '#eee');
}

// Draw the ball
function drawBall() {
	colorCircle(ballX, ballY, ballR, '#777');
}

// Draw the map's bricks
function drawMapBricks(currentMap) {
	for (var i = 0; i < maps[currentMap].bricks.length; i++) {
		// Check if brick exists, if not skip
		if (maps[currentMap].bricks[i].hits >= maps[currentMap].brickColors.length) {
			continue;
		}
		// Border
		colorRect(maps[currentMap].bricks[i].x, maps[currentMap].bricks[i].y,
			maps[currentMap].brickWidth, maps[currentMap].brickHeight, maps[currentMap].brickColors[maps[currentMap].bricks[i].hits]);
		// Fill
		colorRect(maps[currentMap].bricks[i].x + maps[currentMap].brickBorderWidth,
			maps[currentMap].bricks[i].y + maps[currentMap].brickBorderWidth,
			maps[currentMap].brickWidth - (maps[currentMap].brickBorderWidth * 2),
			maps[currentMap].brickHeight - (maps[currentMap].brickBorderWidth * 2), maps[currentMap].brickColors[maps[currentMap].bricks[i].hits]);
	}
}



/**
 * Sprite draw funcitons
 */

// Draw the lava sprite
function drawLava() {
	for (var i = 0; i < canvas.width; i += lavaSpriteWidth) {
		ctx.drawImage(lavaImg, (lavaCounter % 5) * 128 + 10, 115, 100, 20, i, canvas.height - lavaSpriteHeight, 200, 20);
	}
	lavaSpriteTick++;
	if (lavaSpriteTick % 20 === 0) {
		lavaCounter++;
	}
}



/**
 * Simple draw functions
 */

// Rectangle
function colorRect(leftX, topY, width, height, drawColor) {
	ctx.fillStyle = drawColor;
	ctx.fillRect(leftX, topY, width, height);
}

// Circle
function colorCircle(centerX, centerY, radius, drawColor) {
	ctx.fillStyle = drawColor;
	ctx.beginPath();
	ctx.arc(centerX, centerY, radius, 0, Math.PI*2, true);
	ctx.fill();
}


