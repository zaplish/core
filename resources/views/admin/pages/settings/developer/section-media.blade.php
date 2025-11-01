<div class="tab-content__container">
    <div class="settings__wrapper">
        <div class="settings__container">
            <div class="settings__title">Ordner-Struktur</div>
            <div class="settings__items">
                <div class="settings__item">
                    <div class="settings__label">Ohne Unterordner</div>
                    <div class="monospace">/7f0a0d6b-5d41-4f4e-96a9-8cf2aab3845d</div>
                    @if ($mediaSettings['folderStructure'] === 'none')
                        <span style="color: green;">YES</span>
                    @else
                        <span style="color: red;">NO</span>
                    @endif
                    <div class="link" data-set-media-folder-structure="none">SET</div>
                    <br>
                    <br>
                    <div class="settings__label">Jahr/Monat</div>
                    <div class="monospace">/{{ date('Y') }}/{{ date('m') }}/7f0a0d6b-5d41-4f4e-96a9-8cf2aab3845d</div>
                    @if ($mediaSettings['folderStructure'] === 'date')
                        <span style="color: green;">YES</span>
                    @else
                        <span style="color: red;">NO</span>
                    @endif
                    <div class="link" data-set-media-folder-structure="date">SET</div>
                    <br>
                    <br>
                    <div class="settings__label">Hash-Ordner</div>
                    <div class="monospace">/7f/0a/7f0a0d6b-5d41-4f4e-96a9-8cf2aab3845d</div>
                    @if ($mediaSettings['folderStructure'] === 'hash')
                        <span style="color: green;">YES</span>
                    @else
                        <span style="color: red;">NO</span>
                    @endif
                    <div class="link" data-set-media-folder-structure="hash">SET</div>
                </div>
            </div>
        </div>
        
        <div class="settings__container">
            <div class="settings__title">Bilder</div>
            <div class="settings__items">
                <div class="settings__item">
                    <div class="settings__label">Bildgrößen</div>
                    <pre>
                        @php
                        print_r(json_decode($mediaSettings['imageVersions'], true));
                        @endphp
                    </pre>
                </div>
                <div class="settings__item">
                    <div class="settings__label">In WebP konvertieren</div>
                    @if ($mediaSettings['convertToWebp'])
                        <span style="color: green;">YES</span>
                    @else
                        <span style="color: red;">NO</span>
                    @endif
                    
                </div>
                <div class="settings__item">
                    <div class="settings__label">Bildqualität</div>
                    {{ $mediaSettings['imageQuality'] }}
                </div>
            </div>
        </div>
    </div>
</div>