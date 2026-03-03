import './bootstrap';

import Alpine from 'alpinejs';

import 'driver.js/dist/driver.css';
import { driver } from "driver.js";

window.driver = driver;

window.Alpine = Alpine;

Alpine.start();
