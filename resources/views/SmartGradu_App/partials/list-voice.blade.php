{{-- Floating voice control (icon only, right) + simple bottom settings --}}
<button type="button" class="sg-voice-fab" data-voice-fab aria-label="Soma majina" title="Soma majina">
	<i class="fa fa-volume-up" aria-hidden="true"></i>
</button>

<div class="sg-voice-sheet" id="sg-voice-sheet" hidden aria-hidden="true" data-voice-sheet>
	<div class="sg-voice-sheet-backdrop" data-voice-sheet-close></div>
	<div class="sg-voice-sheet-panel" role="dialog" aria-modal="true" aria-labelledby="sg-voice-sheet-title">
		<div class="sg-voice-sheet-handle" aria-hidden="true"></div>
		<div class="sg-voice-sheet-head">
			<h3 class="sg-voice-sheet-title" id="sg-voice-sheet-title" data-i18n="voiceOptions">Voice settings</h3>
			<button type="button" class="sg-voice-sheet-close" data-voice-sheet-close aria-label="Close">&times;</button>
		</div>

		<label class="sg-voice-field">
			<span data-i18n="voiceGender">Voice</span>
			<div class="sg-voice-gender" role="group" aria-label="Voice">
				<button type="button" class="sg-voice-gender-btn is-active" data-voice-gender="female" data-i18n="femaleVoice">Girl</button>
				<button type="button" class="sg-voice-gender-btn" data-voice-gender="male" data-i18n="maleVoice">Male</button>
			</div>
		</label>

		<label class="sg-voice-field">
			<span data-i18n="voiceSpeed">Speed</span>
			<div class="sg-voice-speed" role="group" aria-label="Speed">
				<button type="button" class="sg-voice-speed-btn" data-voice-speed="0.85" data-i18n="speedSlow">Slow</button>
				<button type="button" class="sg-voice-speed-btn is-active" data-voice-speed="1" data-i18n="speedNormal">Normal</button>
				<button type="button" class="sg-voice-speed-btn" data-voice-speed="1.15" data-i18n="speedFast">Fast</button>
			</div>
		</label>

		<div class="sg-voice-sheet-actions">
			<button type="button" class="sg-voice-play" data-voice-play>
				<i class="fa fa-play" aria-hidden="true"></i>
				<span data-i18n="playNames">Play names</span>
			</button>
			<button type="button" class="sg-voice-stop" data-voice-stop hidden>
				<i class="fa fa-stop" aria-hidden="true"></i>
				<span data-i18n="stop">Stop</span>
			</button>
		</div>
	</div>
</div>
