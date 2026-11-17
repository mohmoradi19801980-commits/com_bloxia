<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

$baseUri = Uri::root();
?>

<!-- Import map for Local Three.js -->
<script type="importmap">
    {
        "imports": {
            "three": "<?php echo $baseUri; ?>media/com_bloxia/js/three/three.module.js",
            "three/addons/controls/OrbitControls.js": "<?php echo $baseUri; ?>media/com_bloxia/js/three/OrbitControls.js"
        }
    }
</script>

<script src="https://cdn.tailwindcss.com"></script>
<style>
#canvas-container { 
        position: fixed; /* تغییر از absolute به fixed */
        top: 0; 
        left: 0; 
        width: 100vw; 
        height: 100vh; 
        z-index: 9998; /* قرارگیری روی تمام عناصر جوملا */
    }
    #ui-layer { 
        position: fixed; /* تغییر از absolute به fixed */
        top: 0; 
        left: 0; 
        width: 100vw; 
        height: 100vh; 
        z-index: 9999; 
        pointer-events: none; 
        display: flex; 
        justify-content: space-between; 
        padding: 20px; 
        box-sizing: border-box; 
    }
    body { margin: 0; overflow: hidden; background-color: #dcdde1; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    #canvas-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
    #ui-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; pointer-events: none; display: flex; justify-content: space-between; padding: 20px; box-sizing: border-box; }
    .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); pointer-events: auto; }
    input[type=range] { -webkit-appearance: none; width: 100%; background: transparent; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%; background: #3b82f6; cursor: pointer; margin-top: -6px; }
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: rgba(255, 255, 255, 0.2); border-radius: 2px; }
</style>

<div id="canvas-container"></div>

<div id="ui-layer">
    <!-- Left Side: Navigation -->
    <div class="glass-panel w-16 md:w-20 rounded-2xl flex flex-col items-center py-6 gap-6 shadow-2xl h-fit">
        <button onclick="window.simsEngine.focusCameraOn('fullBody')" class="p-3 bg-white/5 hover:bg-white/20 rounded-xl transition-all group relative" title="تمام قد">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </button>
        <button onclick="window.simsEngine.focusCameraOn('head')" class="p-3 bg-white/5 hover:bg-white/20 rounded-xl transition-all" title="سر و صورت">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </button>
    </div>

    <!-- Right Side: Edit Panel -->
    <div class="flex flex-col gap-4 w-72 md:w-80 h-fit">
        <div class="glass-panel rounded-2xl p-5 shadow-2xl flex flex-col gap-5">
            <h2 class="text-xl font-bold border-b border-white/10 pb-2 text-right text-white">تنظیمات کاراکتر</h2>
            
            <div class="flex flex-col gap-2 text-right">
                <label class="text-sm text-slate-300">رنگ پوست</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="skinColor" value="#e0ac69" class="w-8 h-8 rounded cursor-pointer bg-transparent border-none">
                </div>
            </div>
            <div class="flex flex-col gap-2 text-right">
                <label class="text-sm text-slate-300">رنگ بالاتنه</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="topColor" value="#2d3436" class="w-8 h-8 rounded cursor-pointer bg-transparent border-none">
                </div>
            </div>
            <div class="flex flex-col gap-2 text-right">
                <label class="text-sm text-slate-300">رنگ پایین‌تنه</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="bottomColor" value="#0984e3" class="w-8 h-8 rounded cursor-pointer bg-transparent border-none">
                </div>
            </div>
        </div>

        <button id="saveBtn" class="glass-panel w-full py-4 rounded-xl font-bold text-lg hover:bg-blue-600 transition-colors text-white">
            ذخیره و ورود به بازی
        </button>
    </div>
</div>

<!-- توکن‌های جوملا برای ارتباط امن -->
<input type="hidden" id="csrf_token" name="<?php echo Session::getFormToken(); ?>" value="1">
<input type="hidden" id="ajax_url" value="<?php echo $baseUri . 'index.php?option=com_bloxia&format=json&task=character.save'; ?>">

<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

    // کپی دقیق کدهای کلاس SimsCASEngine شما در اینجا[cite: 6]
    class SimsCASEngine {
        constructor(containerId) {
            this.container = document.getElementById(containerId);
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color('#e2e8f0'); 
            this.camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 100);
            this.renderer = new THREE.WebGLRenderer({ antialias: true });
            this.renderer.setSize(window.innerWidth, window.innerHeight);
            this.container.appendChild(this.renderer.domElement);
            
            // State
            this.characterState = {
                aesthetics: { skin_color: "#e0ac69", top_color: "#2d3436", bottom_color: "#0984e3" }
            };

            this.cameraViews = {
                fullBody: { pos: new THREE.Vector3(0, 1.2, 4), target: new THREE.Vector3(0, 1.0, 0) },
                head: { pos: new THREE.Vector3(0, 1.8, 1.2), target: new THREE.Vector3(0, 1.7, 0) }
            };
            this.targetCameraPos = new THREE.Vector3();
            this.targetControlFocus = new THREE.Vector3();
            this.init();
        }

        init() {
            this.setupControls();
            this.setupLighting();
            this.buildProceduralCharacter();
            window.addEventListener('resize', () => {
                this.camera.aspect = window.innerWidth / window.innerHeight;
                this.camera.updateProjectionMatrix();
                this.renderer.setSize(window.innerWidth, window.innerHeight);
            });
            this.animate();
        }

        setupControls() {
            this.controls = new OrbitControls(this.camera, this.renderer.domElement);
            this.camera.position.copy(this.cameraViews.fullBody.pos);
            this.controls.target.copy(this.cameraViews.fullBody.target);
            this.controls.update();
        }

        setupLighting() {
            const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.6);
            hemiLight.position.set(0, 20, 0);
            this.scene.add(hemiLight);
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
            dirLight.position.set(3, 5, 4);
            this.scene.add(dirLight);
        }

        buildProceduralCharacter() {
            this.mats = {
                skin: new THREE.MeshStandardMaterial({ color: this.characterState.aesthetics.skin_color }),
                top: new THREE.MeshStandardMaterial({ color: this.characterState.aesthetics.top_color }),
                bottom: new THREE.MeshStandardMaterial({ color: this.characterState.aesthetics.bottom_color })
            };
            const headGeo = new THREE.SphereGeometry(0.12, 32, 32);
            const head = new THREE.Mesh(headGeo, this.mats.skin);
            head.position.set(0, 1.65, 0);
            this.scene.add(head);

            const torsoGeo = new THREE.BoxGeometry(0.35, 0.5, 0.2);
            const torso = new THREE.Mesh(torsoGeo, this.mats.top);
            torso.position.set(0, 1.2, 0);
            this.scene.add(torso);
            
            const legGeo = new THREE.CylinderGeometry(0.07, 0.05, 0.8, 16);
            const leftLeg = new THREE.Mesh(legGeo, this.mats.bottom);
            leftLeg.position.set(-0.09, 0.55, 0);
            this.scene.add(leftLeg);
        }

        updateColor(part, hexColor) {
            if(this.mats[part]) {
                this.mats[part].color.set(hexColor);
                if(part === 'skin') this.characterState.aesthetics.skin_color = hexColor;
                if(part === 'top') this.characterState.aesthetics.top_color = hexColor;
                if(part === 'bottom') this.characterState.aesthetics.bottom_color = hexColor;
            }
        }

        focusCameraOn(viewName) {
            this.camera.position.copy(this.cameraViews[viewName].pos);
            this.controls.target.copy(this.cameraViews[viewName].target);
            this.controls.update();
        }

        animate() {
            requestAnimationFrame(this.animate.bind(this));
            this.renderer.render(this.scene, this.camera);
        }
    }

    window.simsEngine = new SimsCASEngine('canvas-container');

    const bindColorInput = (inputId, part) => {
        document.getElementById(inputId).addEventListener('input', (e) => {
            window.simsEngine.updateColor(part, e.target.value);
        });
    };
    bindColorInput('skinColor', 'skin');
    bindColorInput('topColor', 'top');
    bindColorInput('bottomColor', 'bottom');

    // سیستم جدید ذخیره‌سازی از طریق AJAX جوملا
    document.getElementById('saveBtn').addEventListener('click', () => {
        const btn = document.getElementById('saveBtn');
        btn.innerText = 'در حال ذخیره...';
        
        const stateJson = JSON.stringify(window.simsEngine.characterState);
        const csrfToken = document.getElementById('csrf_token').name;
        const ajaxUrl = document.getElementById('ajax_url').value;

        const formData = new FormData();
        formData.append('character_data', stateJson);
        formData.append(csrfToken, '1');

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // انتقال به صفحه اصلی بازی
                window.location.href = data.data.redirect;
            } else {
                alert('خطا در ذخیره کاراکتر: ' + data.message);
                btn.innerText = 'ذخیره و ورود به بازی';
            }
        });
    });
</script>