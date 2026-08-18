// firebase.js
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

const firebaseConfig = {
  apiKey: "AIzaSyBMiS5OLe-SNwltI8MyqUTMyguuHUDwp5k",
  authDomain: "lebak-ciherang.firebaseapp.com",
  projectId: "lebak-ciherang",
  storageBucket: "lebak-ciherang.firebasestorage.app",
  messagingSenderId: "682652605498",
  appId: "1:682652605498:web:ea662d95575fe7f6142903",
  measurementId: "G-SSRP784J6Q"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = typeof window !== 'undefined' ? getAnalytics(app) : null;

export { app, analytics };