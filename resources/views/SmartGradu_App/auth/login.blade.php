@extends('SmartGradu_App.layouts.app')

@section('title', 'Login | ' . config('app.name'))

@section('content')
<section class="sg-login-page" style="--sg-login-bg: url('{{ asset('SmartGradu_App/assets/img/graduation_image.jpeg') }}')">
	<div class="sg-login-card">
		<form class="sg-login-form" method="post" action="{{ route('login') }}" novalidate>
			@csrf

			<div class="sg-login-head">
				<span class="sg-login-head-icon" aria-hidden="true">
					<i class="fa fa-user"></i>
				</span>
				<h1 data-i18n="signIn">Sign in</h1>
			</div>

			@if ($errors->any())
				<div class="sg-login-alert" role="alert">
					{{ $errors->first() }}
				</div>
			@endif

			<label class="sg-login-field">
				<span data-i18n="email">Email</span>
				<input
					type="email"
					name="email"
					class="form-control @error('email') is-invalid @enderror"
					value="{{ old('email') }}"
					required
					autofocus
					autocomplete="username"
					placeholder="name@mocu.ac.tz"
				>
			</label>

			<div class="sg-login-field">
				<span data-i18n="password">Password</span>
				<div class="sg-login-password-row">
					<input
						type="password"
						name="password"
						id="login-password"
						class="form-control @error('password') is-invalid @enderror"
						required
						autocomplete="current-password"
						placeholder="••••••••"
					>
					<button
						type="button"
						class="sg-login-password-toggle"
						data-password-toggle
						data-target="login-password"
						aria-label="Show password"
						aria-pressed="false"
					>
						<i class="fa fa-eye" aria-hidden="true" data-password-icon></i>
					</button>
				</div>
			</div>

			<label class="sg-login-remember">
				<input type="checkbox" name="remember" value="1" @checked(old('remember'))>
				<span data-i18n="rememberMe">Stay Signed in</span>
			</label>

			<button type="submit" class="sg-login-submit" data-login-submit>
				<i class="fa fa-sign-in" aria-hidden="true" data-login-icon></i>
				<span data-i18n="signIn" data-login-label>Sign in</span>
			</button>
		</form>
	</div>
</section>
@endsection

@push('scripts')
<script>
(function () {
	document.querySelectorAll("[data-password-toggle]").forEach(function (btn) {
		btn.addEventListener("click", function () {
			var input = document.getElementById(btn.getAttribute("data-target") || "");
			var icon = btn.querySelector("[data-password-icon]");
			if (!input) return;
			var show = input.type === "password";
			input.type = show ? "text" : "password";
			btn.setAttribute("aria-pressed", show ? "true" : "false");
			btn.setAttribute("aria-label", show ? "Hide password" : "Show password");
			if (icon) {
				icon.classList.toggle("fa-eye", !show);
				icon.classList.toggle("fa-eye-slash", show);
			}
		});
	});

	var form = document.querySelector(".sg-login-form");
	var submitBtn = document.querySelector("[data-login-submit]");
	if (form && submitBtn) {
		form.addEventListener("submit", function () {
			if (submitBtn.classList.contains("is-loading")) return;
			if (form.checkValidity && !form.checkValidity()) return;

			var pack = window.sgGetLangPack ? window.sgGetLangPack() : {};
			var icon = submitBtn.querySelector("[data-login-icon]");
			var label = submitBtn.querySelector("[data-login-label]");

			submitBtn.classList.add("is-loading");
			submitBtn.disabled = true;
			submitBtn.setAttribute("aria-busy", "true");
			if (icon) icon.className = "fa fa-spinner fa-spin";
			if (label) label.textContent = pack.signingIn || "Signing in...";
		});
	}

	var body = document.body;
	if (!body.classList.contains("sg-login-shell")) return;

	var page = document.querySelector(".sg-login-page");
	var baseline = Math.max(
		window.innerHeight || 0,
		window.visualViewport ? window.visualViewport.height : 0
	);

	function applyViewportVars() {
		var vv = window.visualViewport;
		var height = vv ? vv.height : window.innerHeight;
		var top = vv ? vv.offsetTop : 0;
		body.style.setProperty("--sg-vv-height", Math.round(height) + "px");
		body.style.setProperty("--sg-vv-top", Math.round(top) + "px");
		return height;
	}

	function setKeyboardOpen(open) {
		body.classList.toggle("is-keyboard-open", !!open);
		if (!open) {
			body.style.removeProperty("--sg-vv-height");
			body.style.removeProperty("--sg-vv-top");
			return;
		}
		applyViewportVars();
		if (page) {
			window.requestAnimationFrame(function () {
				page.scrollTop = 0;
			});
		}
	}

	function syncKeyboard() {
		var focused = document.activeElement;
		var onField = !!(focused && focused.matches && focused.matches("input, textarea, select"));
		var height = applyViewportVars();
		var shrunk = height < baseline - 100;
		setKeyboardOpen(onField || shrunk);
	}

	["focusin", "focusout"].forEach(function (evt) {
		document.addEventListener(evt, function () {
			window.setTimeout(syncKeyboard, 80);
		});
	});

	if (window.visualViewport) {
		window.visualViewport.addEventListener("resize", function () {
			baseline = Math.max(baseline, window.visualViewport.height);
			syncKeyboard();
		});
		window.visualViewport.addEventListener("scroll", function () {
			if (body.classList.contains("is-keyboard-open")) {
				applyViewportVars();
			}
		});
	} else {
		window.addEventListener("resize", syncKeyboard);
	}

	window.setTimeout(syncKeyboard, 0);
})();
</script>
@endpush
