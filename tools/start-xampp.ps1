param(
    [int]$Port = 8088,
    [string]$XamppRoot = 'C:\xampp',
    [string]$PhpRoot = 'C:\php85'
)
$ErrorActionPreference = 'Stop'
$siteRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$runtime = Join-Path $siteRoot 'storage\framework\xampp'
$httpd = Join-Path $XamppRoot 'apache\bin\httpd.exe'
$apacheRoot = (Join-Path $XamppRoot 'apache').Replace('\','/')
if ($Port -lt 1024 -or $Port -gt 65535) { throw 'Choose a local port between 1024 and 65535.' }
foreach ($file in @($httpd, (Join-Path $PhpRoot 'php8apache2_4.dll'))) {
    if (-not (Test-Path -LiteralPath $file)) { throw "Missing required file: $file" }
}
$existingPidFile = Join-Path $siteRoot 'storage\framework\xampp-httpd.pid'
if (Test-Path -LiteralPath $existingPidFile) {
    $existingId = [int](Get-Content -LiteralPath $existingPidFile)
    $existing = Get-CimInstance Win32_Process -Filter "ProcessId = $existingId"
    if ($existing -and $existing.CommandLine -like "*$runtime*") {
        throw 'This project Apache is already running. Use tools/stop-xampp.ps1 first.'
    }
}
New-Item -ItemType Directory -Path $runtime -Force | Out-Null
$utf8 = New-Object System.Text.UTF8Encoding($false)
foreach ($name in @('httpd.conf', 'php.ini')) {
    $template = [IO.File]::ReadAllText((Join-Path $siteRoot "deploy\xampp\$name.template"))
    $content = $template.Replace('@SITE_ROOT@', $siteRoot.Replace('\','/')).Replace('@APACHE_ROOT@', $apacheRoot).Replace('@PHP_ROOT@', $PhpRoot.Replace('\','/')).Replace('@PORT@', "$Port")
    [IO.File]::WriteAllText((Join-Path $runtime $name), $content, $utf8)
}
$configFile = Join-Path $runtime 'httpd.conf'
& $httpd -t -f $configFile
if ($LASTEXITCODE -ne 0) { throw 'Apache configuration check failed.' }
$instance = Start-Process -FilePath $httpd -ArgumentList @('-f', ('"' + $configFile + '"')) -WorkingDirectory $PhpRoot -WindowStyle Hidden -PassThru
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    Start-Sleep -Milliseconds 200
    $instance.Refresh()
    if ($instance.HasExited) { throw 'Apache exited. Check storage/logs/xampp-apache-error.log.' }
    try {
        $response = Invoke-WebRequest -UseBasicParsing -Uri "http://127.0.0.1:$Port/up" -TimeoutSec 2
        if ($response.StatusCode -eq 200) {
            Write-Output "XAMPP Apache is running at http://127.0.0.1:$Port (project PID $($instance.Id))."
            exit 0
        }
    } catch {}
}
throw 'Apache started but the Laravel health check did not respond. Check project logs.'
