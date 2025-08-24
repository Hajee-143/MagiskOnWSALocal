<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
	<div class="container">
		<a class="navbar-brand fw-bold" href="/index.php">Payroll</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
				<li class="nav-item">
					<a class="nav-link" href="/index.php?page=home">Home</a>
				</li>
				<?php if (function_exists('is_logged_in') && is_logged_in()): ?>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=dashboard">Dashboard</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=employees">Employees</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=departments">Departments</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=pay_periods">Pay Periods</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=payroll">Payroll</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=payslips">Payslips</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=logout">Logout</a></li>
				<?php else: ?>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=login">Login</a></li>
					<li class="nav-item"><a class="nav-link" href="/index.php?page=register">Register</a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</nav>
