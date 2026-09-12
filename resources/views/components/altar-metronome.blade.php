<div
    x-data="{
        isOpen: false,
        isPlaying: false,
        bpm: 120,
        timeSignature: '4/4',
        beatsPerMeasure: 4,
        currentBeat: 0,
        audioCtx: null,
        timerWorker: null,
        nextNoteTime: 0.0,
        scheduleAheadTime: 0.1,
        lookahead: 25,
        timerId: null,
        tapTimes: [],
        flashBeat: false,

        init() {
            this.updateBeatsPerMeasure();
            window.addEventListener('open-altar-metronome', (e) => {
                if (e.detail?.bpm) {
                    this.bpm = Math.min(220, Math.max(40, parseInt(e.detail.bpm, 10)));
                }
                if (e.detail?.timeSignature) {
                    this.timeSignature = e.detail.timeSignature;
                    this.updateBeatsPerMeasure();
                }
                this.isOpen = true;
            });
            window.addEventListener('keydown', (e) => {
                if (this.isOpen && e.key === 'Escape') {
                    this.isOpen = false;
                }
            });
        },

        updateBeatsPerMeasure() {
            const parts = this.timeSignature.split('/');
            this.beatsPerMeasure = parseInt(parts[0], 10) || 4;
        },

        ensureAudioContext() {
            if (!this.audioCtx) {
                const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
                this.audioCtx = new AudioCtxClass();
            }
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
        },

        togglePlay() {
            this.ensureAudioContext();
            this.isPlaying = !this.isPlaying;
            if (this.isPlaying) {
                this.currentBeat = 0;
                this.nextNoteTime = this.audioCtx.currentTime + 0.05;
                this.scheduler();
            } else {
                clearTimeout(this.timerId);
                this.flashBeat = false;
            }
        },

        scheduler() {
            if (!this.isPlaying) return;
            while (this.nextNoteTime < this.audioCtx.currentTime + this.scheduleAheadTime) {
                this.scheduleNote(this.currentBeat, this.nextNoteTime);
                this.nextNote();
            }
            this.timerId = setTimeout(() => this.scheduler(), this.lookahead);
        },

        nextNote() {
            const secondsPerBeat = 60.0 / this.bpm;
            this.nextNoteTime += secondsPerBeat;
            this.currentBeat = (this.currentBeat + 1) % this.beatsPerMeasure;
        },

        scheduleNote(beatNumber, time) {
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();

            osc.connect(gain);
            gain.connect(this.audioCtx.destination);

            const isAccent = beatNumber === 0;
            osc.frequency.value = isAccent ? 1200 : 800;

            gain.gain.setValueAtTime(0.7, time);
            gain.gain.exponentialRampToValueAtTime(0.001, time + 0.06);

            osc.start(time);
            osc.stop(time + 0.07);

            const delay = Math.max(0, (time - this.audioCtx.currentTime) * 1000);
            setTimeout(() => {
                if (this.isPlaying) {
                    this.flashBeat = true;
                    setTimeout(() => this.flashBeat = false, 90);
                }
            }, delay);
        },

        tapTempo() {
            const now = performance.now();
            if (this.tapTimes.length > 0 && (now - this.tapTimes[this.tapTimes.length - 1] > 2200)) {
                this.tapTimes = [];
            }
            this.tapTimes.push(now);
            if (this.tapTimes.length > 5) {
                this.tapTimes.shift();
            }
            if (this.tapTimes.length >= 2) {
                let intervals = [];
                for (let i = 1; i < this.tapTimes.length; i++) {
                    intervals.push(this.tapTimes[i] - this.tapTimes[i - 1]);
                }
                const avgInterval = intervals.reduce((a, b) => a + b, 0) / intervals.length;
                const calculatedBpm = Math.round(60000 / avgInterval);
                this.bpm = Math.min(220, Math.max(40, calculatedBpm));
            }
        },

        adjustBpm(amount) {
            this.bpm = Math.min(220, Math.max(40, this.bpm + amount));
        }
    }"
    x-show="isOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-[100000] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
    style="display: none; z-index: 100000 !important;"
>
    <!-- Modal Container -->
    <div 
        @click.outside="isOpen = false"
        class="w-full max-w-sm rounded-3xl bg-[#12141a] border border-[#1e222c] p-6 shadow-2xl shadow-cyan-500/10 text-white flex flex-col gap-5 relative"
    >
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#00d2ff] shadow-[0_0_8px_#00d2ff]"></span>
                <h3 class="text-xs font-black uppercase tracking-widest text-[#71788e]">ALTAR METRONOME</h3>
            </div>
            <button 
                @click="isOpen = false"
                class="w-8 h-8 rounded-full bg-[#181b24] border border-[#1e222c] text-slate-400 hover:text-white flex items-center justify-center tap-scale cursor-pointer"
            >
                ✕
            </button>
        </div>

        <!-- BPM Big Display & LED Pulser -->
        <div class="flex flex-col items-center justify-center py-4 bg-[#08080a] border border-[#1e222c] rounded-2xl relative overflow-hidden">
            <!-- Pulsing Beat Halo -->
            <div 
                class="absolute inset-0 transition-opacity duration-75 pointer-events-none"
                :class="flashBeat ? 'bg-[#00d2ff]/15 opacity-100' : 'opacity-0'"
            ></div>

            <span class="text-[10px] font-black uppercase tracking-widest text-[#71788e] mb-1">ANDAMENTO (BPM)</span>
            
            <div class="flex items-baseline gap-2">
                <span 
                    class="text-6xl font-black font-mono tracking-tight transition-colors duration-75"
                    :class="flashBeat ? 'text-[#00d2ff]' : 'text-white'"
                    x-text="bpm"
                ></span>
                <span class="text-xs font-mono font-bold text-[#71788e]">BPM</span>
            </div>

            <!-- Visual Beat Counter Indicator -->
            <div class="flex items-center gap-2 mt-3">
                <template x-for="i in beatsPerMeasure" :key="i">
                    <span 
                        class="w-3 h-3 rounded-full transition-all duration-75"
                        :class="(currentBeat === (i - 1) && flashBeat) 
                            ? (i === 1 ? 'bg-[#00e676] shadow-[0_0_10px_#00e676] scale-125' : 'bg-[#00d2ff] shadow-[0_0_8px_#00d2ff] scale-125') 
                            : 'bg-[#1e222c]'"
                    ></span>
                </template>
            </div>
        </div>

        <!-- Fine Tuning Controls -->
        <div class="flex items-center justify-between gap-2">
            <button 
                @click="adjustBpm(-5)" 
                class="flex-1 py-2 rounded-xl bg-[#181b24] border border-[#1e222c] text-xs font-mono font-bold text-slate-300 hover:text-white tap-scale cursor-pointer"
            >
                -5
            </button>
            <button 
                @click="adjustBpm(-1)" 
                class="flex-1 py-2 rounded-xl bg-[#181b24] border border-[#1e222c] text-sm font-mono font-bold text-slate-300 hover:text-white tap-scale cursor-pointer"
            >
                -1
            </button>
            <button 
                @click="adjustBpm(1)" 
                class="flex-1 py-2 rounded-xl bg-[#181b24] border border-[#1e222c] text-sm font-mono font-bold text-slate-300 hover:text-white tap-scale cursor-pointer"
            >
                +1
            </button>
            <button 
                @click="adjustBpm(5)" 
                class="flex-1 py-2 rounded-xl bg-[#181b24] border border-[#1e222c] text-xs font-mono font-bold text-slate-300 hover:text-white tap-scale cursor-pointer"
            >
                +5
            </button>
        </div>

        <!-- Slider -->
        <div class="space-y-1">
            <input 
                type="range" 
                min="40" 
                max="220" 
                x-model.number="bpm" 
                class="w-full h-1.5 bg-[#181b24] rounded-lg appearance-none cursor-pointer accent-[#00d2ff]"
            >
            <div class="flex justify-between text-[10px] font-mono text-[#71788e]">
                <span>40 Lento</span>
                <span>120 Moderato</span>
                <span>220 Presto</span>
            </div>
        </div>

        <!-- Compasso Selector & Tap Tempo -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Time Signature -->
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-[#71788e] block mb-1">Compasso</label>
                <select 
                    x-model="timeSignature" 
                    @change="updateBeatsPerMeasure()"
                    class="w-full bg-[#181b24] border border-[#1e222c] rounded-xl px-3 py-2 text-xs font-mono font-bold text-white focus:outline-none focus:border-[#00d2ff]"
                >
                    <option value="4/4">4/4 Comum</option>
                    <option value="3/4">3/4 Valsa</option>
                    <option value="2/4">2/4 Marcha</option>
                    <option value="6/8">6/8 Composto</option>
                    <option value="12/8">12/8 Balada</option>
                </select>
            </div>

            <!-- Tap Tempo Button -->
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-[#71788e] block mb-1">Tap Tempo</label>
                <button 
                    @click="tapTempo()"
                    class="w-full bg-[#181b24] hover:bg-[#1e222c] border border-[#1e222c] rounded-xl py-2 text-xs font-black uppercase tracking-wider text-[#00d2ff] shadow-sm tap-scale cursor-pointer"
                >
                    TAP
                </button>
            </div>
        </div>

        <!-- Big Play / Stop Action Button -->
        <button 
            @click="togglePlay()"
            class="w-full py-3.5 rounded-2xl font-black text-sm uppercase tracking-wider flex items-center justify-center gap-2 tap-scale cursor-pointer transition-all duration-200"
            :class="isPlaying 
                ? 'bg-[#181b24] border border-rose-500/40 text-rose-400 hover:bg-rose-950/20' 
                : 'bg-[#00d2ff] hover:bg-[#38bdf8] text-black shadow-lg shadow-cyan-500/20'"
        >
            <template x-if="isPlaying">
                <span class="flex items-center gap-2">
                    <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                    Pausar Metrônomo
                </span>
            </template>
            <template x-if="!isPlaying">
                <span class="flex items-center gap-2">
                    <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Iniciar Metrônomo
                </span>
            </template>
        </button>
    </div>
</div>
