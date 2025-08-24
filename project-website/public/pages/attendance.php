<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$user = current_user();
$error = '';
$notice = '';

if (is_employee() && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$status = $_POST['status'] ?? 'present';
	$lat = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
	$lng = isset($_POST['lng']) ? (float)$_POST['lng'] : null;
	$photoPath = null;

	// Save base64 photo if provided
	if (!empty($_POST['photo_base64'])) {
		$raw = $_POST['photo_base64'];
		if (preg_match('/^data:image\/(png|jpeg);base64,/', $raw)) {
			$raw = preg_replace('/^data:image\/(png|jpeg);base64,/', '', $raw);
			$bin = base64_decode($raw, true);
			if ($bin !== false) {
				$fname = 'att_' . time() . '_' . (int)$user['id'] . '.png';
				$path = DATA_PATH . '/' . $fname;
				file_put_contents($path, $bin);
				$photoPath = $fname;
			}
		}
	}

	$stmt = $db->prepare('INSERT INTO attendance (employee_id, date, status, latitude, longitude, photo_path) VALUES (?,?,?,?,?,?)');
	$stmt->execute([(int)$user['id'], date('Y-m-d'), $status, $lat, $lng, $photoPath]);
	$notice = 'Attendance recorded';

	// Geofence check placeholder: if outside, send email alert
	$geofenceCenterLat = 0.0; // configure
	$geofenceCenterLng = 0.0; // configure
	$radiusM = $CONFIG['geofence_default_radius_m'] ?? 200;
	if ($lat !== null && $lng !== null) {
		$distance = 999999;
		// Placeholder without haversine since center is 0,0; keep alert example
		if ($geofenceCenterLat == 0.0 && $geofenceCenterLng == 0.0) {
			$distance = $radiusM + 1; // force alert unless configured
		}
		if ($distance > $radiusM && !empty($user['email'])) {
			send_email($user['email'], 'Attendance outside location', 'Your attendance was marked outside the configured location.');
		}
	}
}

// Views
if (is_admin()) {
	$records = $db->query('SELECT a.*, u.first_name, u.last_name FROM attendance a JOIN users u ON u.id=a.employee_id ORDER BY a.id DESC')->fetchAll();
} else {
	$stmt = $db->prepare('SELECT * FROM attendance WHERE employee_id=? ORDER BY id DESC');
	$stmt->execute([(int)$user['id']]);
	$records = $stmt->fetchAll();
}
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Attendance</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<?php if (is_employee()): ?>
	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" onsubmit="return captureAndSubmit(event)" class="row g-3">
				<div class="col-md-3">
					<label class="form-label">Status</label>
					<select name="status" class="form-select">
						<option value="present">Present</option>
						<option value="remote">Remote</option>
						<option value="field">Field</option>
					</select>
				</div>
				<input type="hidden" name="lat" id="lat">
				<input type="hidden" name="lng" id="lng">
				<input type="hidden" name="photo_base64" id="photo_base64">
				<div class="col-md-9">
					<div class="d-flex align-items-center gap-3">
						<video id="video" width="240" height="180" autoplay playsinline class="border rounded"></video>
						<canvas id="canvas" width="240" height="180" class="d-none"></canvas>
						<button class="btn btn-outline-secondary" type="button" onclick="takePhoto()">Capture Face</button>
					</div>
				</div>
				<div class="col-12">
					<button class="btn btn-primary">Mark Attendance</button>
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="d-flex justify-content-between align-items-center">
				<h2 class="h6">Recent Records</h2>
				<?php if (is_admin()): ?><a class="btn btn-sm btn-outline-secondary" href="/index.php?page=attendance_export">Export CSV</a><?php endif; ?>
			</div>
			<div class="table-responsive mt-3">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>ID</th>
							<?php if (is_admin()): ?><th>Employee</th><?php endif; ?>
							<th>Date</th><th>Status</th><th>Lat</th><th>Lng</th><th>Photo</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($records as $r): ?>
							<tr>
								<td><?php echo (int)$r['id']; ?></td>
								<?php if (is_admin()): ?><td><?php echo h(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')); ?></td><?php endif; ?>
								<td><?php echo h($r['date']); ?></td>
								<td><?php echo h($r['status']); ?></td>
								<td><?php echo h((string)$r['latitude']); ?></td>
								<td><?php echo h((string)$r['longitude']); ?></td>
								<td>
									<?php if (!empty($r['photo_path'])): ?>
										<img src="<?php echo '/data/' . h($r['photo_path']); ?>" width="64" height="48" class="rounded border" />
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>

<script>
async function initGeolocation() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition((pos) => {
			document.getElementById('lat').value = pos.coords.latitude;
			document.getElementById('lng').value = pos.coords.longitude;
		});
	}
}

async function initCamera() {
	try {
		const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
		document.getElementById('video').srcObject = stream;
	} catch (e) {
		console.warn('Camera not available', e);
	}
}

function takePhoto() {
	const video = document.getElementById('video');
	const canvas = document.getElementById('canvas');
	const ctx = canvas.getContext('2d');
	ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
	const data = canvas.toDataURL('image/png');
	document.getElementById('photo_base64').value = data;
}

function captureAndSubmit(e) {
	initGeolocation();
	return true;
}

document.addEventListener('DOMContentLoaded', () => { initGeolocation(); initCamera(); });
</script>
