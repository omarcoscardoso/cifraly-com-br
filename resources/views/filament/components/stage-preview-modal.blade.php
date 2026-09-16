<div class="space-y-4">
    <div class="flex items-center justify-between p-4 rounded-xl bg-[#12141a] border border-[#1e222c]">
        <div>
            <h4 class="text-base font-bold text-white">{{ $title }}</h4>
            <p class="text-xs text-slate-400">{{ $artist }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-lg bg-[#00d2ff]/10 border border-[#00d2ff]/30 text-[#00d2ff] font-bold text-xs">
                Tom: {{ $key }}
            </span>
        </div>
    </div>

    <!-- Preview Container with Stage Typography & Colors -->
    <div class="rounded-xl bg-[#08080a] border border-[#1e222c] p-5 max-h-[60vh] overflow-y-auto no-scrollbar font-mono text-base select-none">
        <style>
            .stage-preview-wrap .stage-chord-line {
                white-space: pre !important;
                font-family: 'JetBrains Mono', monospace !important;
                line-height: 1.25 !important;
                font-weight: 700 !important;
                letter-spacing: 0 !important;
            }
            .stage-preview-wrap .stage-lyric-line {
                white-space: pre !important;
                font-family: 'JetBrains Mono', monospace !important;
                line-height: 1.375 !important;
                margin-bottom: 0.5rem !important;
                color: #e2e8f0 !important;
                letter-spacing: 0 !important;
            }
            .stage-preview-wrap .stage-section-badge {
                font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
                font-weight: 800 !important;
                color: #00d2ff !important;
                background-color: rgba(0, 210, 255, 0.08) !important;
                border-left: 4px solid #00d2ff !important;
                border-top: 1px solid rgba(0, 210, 255, 0.2) !important;
                border-bottom: 1px solid rgba(0, 210, 255, 0.2) !important;
                padding: 0.25rem 0.65rem !important;
                margin: 1rem 0 0.5rem 0 !important;
                border-radius: 6px !important;
                font-size: 0.8em !important;
                display: block !important;
                text-transform: uppercase !important;
            }
            .stage-preview-wrap .stage-section-chorus {
                font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
                font-weight: 800 !important;
                color: #ffb300 !important;
                background-color: rgba(255, 179, 0, 0.12) !important;
                border-left: 4px solid #ffb300 !important;
                border-top: 1px solid rgba(255, 179, 0, 0.25) !important;
                border-bottom: 1px solid rgba(255, 179, 0, 0.25) !important;
                padding: 0.25rem 0.65rem !important;
                margin: 1rem 0 0.5rem 0 !important;
                border-radius: 6px !important;
                font-size: 0.8em !important;
                display: block !important;
                text-transform: uppercase !important;
            }
            .stage-preview-wrap .stage-section-tag {
                font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
                font-weight: 800 !important;
                color: #cbd5e1 !important;
                background-color: rgba(148, 163, 184, 0.1) !important;
                border-left: 4px solid #94a3b8 !important;
                padding: 0.25rem 0.65rem !important;
                margin: 1rem 0 0.5rem 0 !important;
                border-radius: 6px !important;
                font-size: 0.8em !important;
                display: block !important;
                text-transform: uppercase !important;
            }
            .stage-preview-wrap .stage-chord {
                color: #fbbf24 !important;
                font-weight: 700 !important;
                font-family: 'JetBrains Mono', monospace !important;
            }
            .stage-preview-wrap .stage-chord-token {
                color: #fde68a !important;
                font-family: 'JetBrains Mono', monospace !important;
            }
            .stage-preview-wrap .stage-lyric {
                color: #f1f5f9 !important;
                font-family: 'JetBrains Mono', monospace !important;
            }
            .stage-preview-wrap .stage-lyric-chord-line {
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: flex-end !important;
                margin-top: 0.25rem !important;
                margin-bottom: 0.5rem !important;
                line-height: normal !important;
            }
            .stage-preview-wrap .stage-chord-pair {
                display: inline-flex !important;
                flex-direction: column !important;
                justify-content: flex-end !important;
                vertical-align: bottom !important;
                min-width: max-content !important;
                font-family: 'JetBrains Mono', monospace !important;
            }
        </style>

        <div class="stage-preview-wrap">
            {!! $formattedChords !!}
        </div>
    </div>
</div>
