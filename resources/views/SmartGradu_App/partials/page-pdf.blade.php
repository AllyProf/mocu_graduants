@php
	$pdfUrl = $pdfUrl ?? '#';
@endphp
<a
	href="{{ $pdfUrl }}"
	class="sg-pagehead-pdf"
	data-pdf-download
	data-pdf-base="{{ $pdfUrl }}"
	aria-label="Download PDF"
	title="Download PDF"
	onclick="return window.sgDownloadPdf ? window.sgDownloadPdf(event, this) : true"
>
	<span class="sg-pagehead-pdf-badge" aria-hidden="true">
		<svg class="sg-pagehead-pdf-doc" viewBox="0 0 24 24" width="16" height="16" focusable="false">
			<path fill="currentColor" d="M6 2h8l4 4v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm7 1.5V7h3.5L13 3.5z"/>
			<path fill="#fff" d="M8.2 11.2h7.6v6.2H8.2z"/>
			<path fill="currentColor" d="M9.1 12.1h1.55c.72 0 1.18.38 1.18.98 0 .62-.46 1-1.2 1H9.85v1.52H9.1v-3.5zm.75.62v1.12h.7c.3 0 .48-.14.48-.4s-.18-.4-.5-.4h-.68zm2.7-.62h1.7c.95 0 1.55.55 1.55 1.4 0 .86-.6 1.42-1.55 1.42h-.95v1.36h-.75v-4.18zm.75.62v1.72h.9c.5 0 .82-.28.82-.86s-.32-.86-.82-.86h-.9zm3.35-.62h.75v4.18h-.75z"/>
		</svg>
		<span class="sg-pagehead-pdf-label" data-i18n="downloadPdfShort">PDF</span>
	</span>
	<span class="sg-pagehead-pdf-arrow" aria-hidden="true">
		<svg viewBox="0 0 24 24" width="15" height="15" focusable="false">
			<path fill="currentColor" d="M11.25 3.5h1.5v9.2l2.7-2.7 1.06 1.06L12 16.57 7.49 12.06l1.06-1.06 2.7 2.7V3.5z"/>
			<path fill="currentColor" d="M5 18.25h14v1.5H5z"/>
		</svg>
	</span>
</a>
