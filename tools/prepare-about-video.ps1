param(
    [string]$Source = '',
    [string]$Ffmpeg = '',
    [double]$PosterSeconds = 18
)
$ErrorActionPreference = 'Stop'
$siteRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
if (-not $Source) { $Source = Join-Path $siteRoot 'public\RA VLOG.mp4' }
if (-not (Test-Path -LiteralPath $Source)) { throw "Video not found: $Source" }
if (-not $Ffmpeg) {
    $command = Get-Command ffmpeg -ErrorAction SilentlyContinue
    if ($command) { $Ffmpeg = $command.Source }
    else {
        $bundled = Get-ChildItem -LiteralPath (Join-Path $siteRoot 'storage\framework\video-tools\imageio_ffmpeg\binaries') -Filter '*.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($bundled) { $Ffmpeg = $bundled.FullName }
    }
}
if (-not $Ffmpeg) { throw 'Install FFmpeg or pass -Ffmpeg with its executable path. See docs/ABOUT-VIDEO.md.' }
$destination = Join-Path $siteRoot 'storage\app\private\about-video'
New-Item -ItemType Directory -Path $destination -Force | Out-Null
$video = Join-Path $destination 'ra-vlog.mp4'
$poster = Join-Path $destination 'ra-vlog-poster.jpg'
& $Ffmpeg -hide_banner -loglevel warning -y -i $Source -map 0:v:0 -map '0:a:0?' -c:v libx264 -preset medium -crf 21 -pix_fmt yuv420p -maxrate 2500k -bufsize 5000k -g 60 -c:a aac -b:a 128k -movflags +faststart $video
if ($LASTEXITCODE -ne 0) { throw 'Video encoding failed.' }
& $Ffmpeg -hide_banner -loglevel error -y -ss $PosterSeconds -i $video -frames:v 1 -q:v 2 -update 1 $poster
if ($LASTEXITCODE -ne 0) { throw 'Poster extraction failed.' }
$publicVideoFolder = Join-Path $siteRoot 'public\videos'
New-Item -ItemType Directory -Path $publicVideoFolder -Force | Out-Null
Copy-Item -LiteralPath $video -Destination (Join-Path $publicVideoFolder 'ra-vlog.mp4') -Force
Copy-Item -LiteralPath $poster -Destination (Join-Path $siteRoot 'public\assets\ra-vlog-poster.jpg') -Force
Get-Item -LiteralPath $Source,$video,$poster | Select-Object Name,Length
Write-Output 'Prepared playback copy. Next run: php artisan app:import-about-video'
