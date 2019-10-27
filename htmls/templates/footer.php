		<footer class="footer bg-dark">
			<div class="container">
				<div class="left-justify">
					<span>All content, &nbsp; <a href='mailto:me@davpeterson.com'>Dave Peterson</a></span>
				</div>
<?php if(!$alreadyLoggedIn){ ?>
				<div class='right-justify'>
					<button id="login" class="btn">Login</button>
				</div>
<?php }else{ ?>
				<div class='right-justify'>
					<button id="logout" class="btn">Logout</button>
				</div>
<?php } ?>
			</div>
		</footer>

		<div id="loginModal" class="modal">
			<div class="modal-box">
				<div class="close-bar">&nbsp;<span class="close">&times;</span></div>
				<div class="modal-content">
					<label>username <input type='text' id='username'></label>
					<label>password <input type='password' id='password'></label>
					<button id='submit'>Submit</button>
				</div>
			</div>
		</div> 

<script>
$(document).ready(function(){

	//accept modal input
	$('#submit').on('click', function(){
		var username = $('#username').val();
		var password = $('#password').val();
		$.ajax({
			method: 'POST',
			url: '/ajax/login.php',
			data: { username: username, password: password } 
		}).done(function(data){
			//logged in
			window.location.reload();
		});
	});

	//show modal
	$('#login').on('click', function(){
		$('#loginModal').css('display', 'block');
	});

	// When the user clicks on <span> (x), close the modal
	$('.close').on('click', function(){
//		console.warn('click on x');
		$('#loginModal').css('display', 'none');
	});

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event){
//		console.warn('event.target.id', event.target.id);
		if(event.target.id === 'loginModal'){
			$('#loginModal').css('display', 'none');
		}
	}; 	

	//logout
	$('#logout').on('click', function(){
		$.ajax({
			method: 'POST',
			url: '/ajax/login.php',
			data: { logout: true } 
		}).done(function(data){
			//logged out
			window.location.reload();
		});
	});

});
</script>
