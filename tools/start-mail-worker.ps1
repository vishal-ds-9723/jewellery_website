$ErrorActionPreference = 'Stop'
$siteRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$artisan = Join-Path $siteRoot 'artisan'
$php = (Get-Command php -ErrorAction Stop).Source
$existing = Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" | Where-Object {
    $_.CommandLine -and $_.CommandLine.Contains($artisan) -and $_.CommandLine.Contains('app:work-enquiry-mail')
}
if ($existing) {
    Write-Output "The enquiry email worker is already running (PID $($existing.ProcessId))."
    exit 0
}
$worker = Start-Process -FilePath $php -ArgumentList @(('"' + $artisan + '"'), 'app:work-enquiry-mail') -WorkingDirectory $siteRoot -WindowStyle Hidden -RedirectStandardOutput (Join-Path $siteRoot 'storage\logs\enquiry-mail-worker.log') -RedirectStandardError (Join-Path $siteRoot 'storage\logs\enquiry-mail-worker-error.log') -PassThru
for ($attempt = 0; $attempt -lt 15; $attempt++) {
    Start-Sleep -Milliseconds 200
    $worker.Refresh()
    if ($worker.HasExited) { throw 'Email worker exited. Check storage/logs/enquiry-mail-worker*.log for configuration errors.' }
}
Write-Output "Enquiry email worker started (PID $($worker.Id)). Use php artisan queue:restart to stop it gracefully."
