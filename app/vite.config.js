import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'
import composerData from '../composer.json'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [react()],
    base: '/assets/plugins/' + composerData.name + "/"
})
