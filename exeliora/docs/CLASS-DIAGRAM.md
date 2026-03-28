# CLASS DIAGRAM - SISTEM EXELIORA

## Overview
Dokumentasi Class Diagram untuk Sistem Smart Home Exeliora yang mengelola sensor, kontrol cahaya, notifikasi, dan pengaturan sistem.

---

## 📦 PACKAGES & CLASSES

### 1. **User Management Package**

#### Class: `User`
**Attributes:**
- `- userId: String`
- `- username: String`
- `- email: String`
- `- password: String`
- `- role: String`

**Methods:**
- `+ login(): boolean`
- `+ logout(): void`
- `+ updateProfile(): boolean`
- `+ changePassword(): boolean`
- `+ getInfo(): UserInfo`

**Deskripsi:** Mengelola data dan autentikasi pengguna sistem.

---

### 2. **Sensor Management Package**

#### Abstract Class: `Sensor`
**Attributes:**
- `# sensorId: String`
- `# sensorName: String`
- `# sensorType: String`
- `# status: String`
- `# location: String`
- `# lastUpdate: DateTime`

**Methods:**
- `+ activate(): void`
- `+ deactivate(): void`
- `+ readValue(): float`
- `+ updateStatus(): void`
- `+ delete(): boolean`
- `{abstract} + processData(): void`

**Deskripsi:** Base class untuk semua jenis sensor dalam sistem.

---

#### Class: `TemperatureSensor` extends Sensor
**Attributes:**
- `- temperature: float`
- `- minThreshold: float`
- `- maxThreshold: float`
- `- unit: String`

**Methods:**
- `+ getTemperature(): float`
- `+ setThreshold(min: float, max: float): void`
- `+ checkThreshold(): boolean`
- `+ processData(): void`
- `+ viewGraph(): void`
- `+ viewHistory(): void`

**Deskripsi:** Sensor suhu DHT11 untuk monitoring temperatur ruangan.

---

#### Class: `LightSensor` extends Sensor
**Attributes:**
- `- lightIntensity: float`
- `- unit: String`
- `- materialId: String`
- `- roomId: String`

**Methods:**
- `+ getLightIntensity(): float`
- `+ readMaterialId(): String`
- `+ editRoom(roomName: String): void`
- `+ selectRoom(roomId: String): void`
- `+ processData(): void`
- `+ addReading(): void`

**Deskripsi:** Sensor cahaya LDR untuk monitoring intensitas cahaya dan kontrol lampu otomatis.

---

#### Class: `SensorController`
**Attributes:**
- `- sensors: List<Sensor>`

**Methods:**
- `+ addSensor(sensor: Sensor): boolean`
- `+ removeSensor(sensorId: String): boolean`
- `+ getSensor(sensorId: String): Sensor`
- `+ getAllSensors(): List<Sensor>`
- `+ updateSensor(sensor: Sensor): boolean`
- `+ getSensorsByType(type: String): List<Sensor>`

**Deskripsi:** Controller untuk mengelola semua sensor dalam sistem.

---

### 3. **Light Control Package**

#### Class: `Room`
**Attributes:**
- `- roomId: String`
- `- roomName: String`
- `- sensorId: String`

**Methods:**
- `+ setName(name: String): void`
- `+ getName(): String`
- `+ getSensor(): Sensor`

**Deskripsi:** Representasi ruangan dalam sistem.

---

#### Class: `Light`
**Attributes:**
- `- lightId: String`
- `- roomId: String`
- `- status: boolean`
- `- brightness: int`
- `- lastUpdate: DateTime`

**Methods:**
- `+ turnOn(): void`
- `+ turnOff(): void`
- `+ setBrightness(level: int): void`
- `+ getBrightness(): int`
- `+ getStatus(): boolean`
- `+ toggle(): void`

**Deskripsi:** Representasi lampu yang dapat dikontrol.

---

#### Class: `LightController`
**Attributes:**
- `- lights: List<Light>`

**Methods:**
- `+ controlLight(lightId: String, status: boolean): void`
- `+ adjustBrightness(lightId: String, level: int): void`
- `+ getLightsByRoom(roomId: String): List<Light>`
- `+ getAllLights(): List<Light>`
- `+ autoControl(sensorData: float): void`

**Deskripsi:** Controller untuk mengelola kontrol lampu.

---

### 4. **Notification System Package**

#### Class: `Notification`
**Attributes:**
- `- notificationId: String`
- `- title: String`
- `- message: String`
- `- type: String`
- `- timestamp: DateTime`
- `- isRead: boolean`
- `- priority: String`

**Methods:**
- `+ markAsRead(): void`
- `+ getDetails(): NotificationDetails`
- `+ delete(): boolean`

**Deskripsi:** Representasi notifikasi dalam sistem.

---

#### Class: `NotificationManager`
**Attributes:**
- `- notifications: List<Notification>`
- `- settings: NotificationSettings`

**Methods:**
- `+ createNotification(title: String, message: String, type: String): void`
- `+ sendNotification(notification: Notification): boolean`
- `+ getUnreadNotifications(): List<Notification>`
- `+ getAllNotifications(): List<Notification>`
- `+ clearAll(): void`
- `+ updateSettings(settings: NotificationSettings): void`

**Deskripsi:** Mengelola semua notifikasi sistem.

---

#### Class: `NotificationSettings`
**Attributes:**
- `- emailEnabled: boolean`
- `- pushEnabled: boolean`
- `- sensorAlertsEnabled: boolean`

**Methods:**
- `+ toggleEmail(): void`
- `+ togglePush(): void`
- `+ toggleSensorAlerts(): void`
- `+ getSettings(): Map`

**Deskripsi:** Pengaturan notifikasi pengguna.

---

### 5. **System Configuration Package**

#### Class: `SystemSettings`
**Attributes:**
- `- autoMode: boolean`
- `- ecoMode: boolean`
- `- language: String`
- `- timezone: String`

**Methods:**
- `+ enableAutoMode(): void`
- `+ disableAutoMode(): void`
- `+ enableEcoMode(): void`
- `+ disableEcoMode(): void`
- `+ updateSettings(): boolean`

**Deskripsi:** Pengaturan sistem global.

---

#### Class: `Dashboard`
**Attributes:**
- `- userId: String`
- `- widgets: List<Widget>`

**Methods:**
- `+ loadData(): void`
- `+ refreshData(): void`
- `+ getSensorData(): List<SensorData>`
- `+ getSystemStatus(): SystemStatus`

**Deskripsi:** Dashboard utama untuk menampilkan informasi sistem.

---

#### Class: `Widget`
**Attributes:**
- `- widgetId: String`
- `- widgetType: String`
- `- data: Map`

**Methods:**
- `+ update(): void`
- `+ render(): void`

**Deskripsi:** Widget untuk menampilkan data di dashboard.

---

## 🔗 RELATIONSHIPS

### Inheritance (Pewarisan)
- `TemperatureSensor` **extends** `Sensor`
- `LightSensor` **extends** `Sensor`

### Composition (Komposisi)
- `SensorController` **manages** `0..*` `Sensor`
- `LightController` **controls** `0..*` `Light`
- `NotificationManager` **manages** `0..*` `Notification`
- `Dashboard` **contains** `0..*` `Widget`

### Association (Asosiasi)
- `User` **views** `Dashboard` (1:1)
- `User` **receives** `Notification` (1:*)
- `Room` **contains** `Light` (1:*)
- `Room` **monitored by** `LightSensor` (1:0..1)
- `LightSensor` **triggers** `LightController` (1:1)
- `TemperatureSensor` **alerts** `NotificationManager` (1:1)
- `NotificationManager` **uses** `NotificationSettings` (1:1)
- `Dashboard` **queries** `SensorController` (1:1)
- `Dashboard` **displays** `SystemSettings` (1:1)
- `SystemSettings` **configured by** `User` (1:1)

---

## 📊 DIAGRAM VISUAL

```
┌─────────────────────────────────────────────────────────────────────┐
│                        USER MANAGEMENT                              │
│  ┌──────────────────┐                                              │
│  │      User        │                                              │
│  ├──────────────────┤                                              │
│  │ - userId         │                                              │
│  │ - username       │                                              │
│  │ - email          │                                              │
│  │ - password       │                                              │
│  └──────────────────┘                                              │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                     SENSOR MANAGEMENT                               │
│                                                                     │
│         ┌───────────────────┐                                      │
│         │   <<abstract>>    │                                      │
│         │     Sensor        │                                      │
│         ├───────────────────┤                                      │
│         │ # sensorId        │                                      │
│         │ # sensorType      │                                      │
│         │ # status          │                                      │
│         └─────────┬─────────┘                                      │
│                   │                                                 │
│         ┌─────────┴──────────┐                                     │
│         │                    │                                     │
│  ┌──────▼──────────┐  ┌──────▼──────────┐                        │
│  │ Temperature     │  │  LightSensor    │                        │
│  │    Sensor       │  │     (LDR)       │                        │
│  ├─────────────────┤  ├─────────────────┤                        │
│  │ - temperature   │  │ - lightIntensity│                        │
│  │ - minThreshold  │  │ - materialId    │                        │
│  │ - maxThreshold  │  │ - roomId        │                        │
│  └─────────────────┘  └─────────────────┘                        │
│                                                                     │
│  ┌─────────────────────────────┐                                  │
│  │   SensorController          │                                  │
│  ├─────────────────────────────┤                                  │
│  │ - sensors: List<Sensor>     │                                  │
│  │ + addSensor()               │                                  │
│  │ + removeSensor()            │                                  │
│  └─────────────────────────────┘                                  │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      LIGHT CONTROL                                  │
│                                                                     │
│  ┌──────────────┐      ┌──────────────┐      ┌─────────────────┐ │
│  │    Room      │──────│    Light     │      │ LightController │ │
│  ├──────────────┤      ├──────────────┤      ├─────────────────┤ │
│  │ - roomId     │      │ - lightId    │      │ - lights: List  │ │
│  │ - roomName   │      │ - status     │      │ + controlLight()│ │
│  │ - sensorId   │      │ - brightness │      │ + adjustBright()│ │
│  └──────────────┘      └──────────────┘      └─────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                   NOTIFICATION SYSTEM                               │
│                                                                     │
│  ┌────────────────────┐      ┌──────────────────────┐            │
│  │   Notification     │◄─────│ NotificationManager  │            │
│  ├────────────────────┤      ├──────────────────────┤            │
│  │ - notificationId   │      │ - notifications      │            │
│  │ - title            │      │ + createNotif()      │            │
│  │ - message          │      │ + sendNotif()        │            │
│  │ - type             │      └──────────┬───────────┘            │
│  │ - isRead           │                 │                         │
│  └────────────────────┘      ┌──────────▼───────────┐            │
│                              │ NotificationSettings │            │
│                              ├──────────────────────┤            │
│                              │ - emailEnabled       │            │
│                              │ - pushEnabled        │            │
│                              │ - sensorAlertsEnable │            │
│                              └──────────────────────┘            │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                  SYSTEM CONFIGURATION                               │
│                                                                     │
│  ┌─────────────────┐      ┌──────────────┐                        │
│  │  Dashboard      │──────│   Widget     │                        │
│  ├─────────────────┤      ├──────────────┤                        │
│  │ - userId        │      │ - widgetId   │                        │
│  │ - widgets       │      │ - widgetType │                        │
│  │ + loadData()    │      │ + render()   │                        │
│  └─────────────────┘      └──────────────┘                        │
│                                                                     │
│  ┌──────────────────────┐                                         │
│  │  SystemSettings      │                                         │
│  ├──────────────────────┤                                         │
│  │ - autoMode           │                                         │
│  │ - ecoMode            │                                         │
│  │ + enableAutoMode()   │                                         │
│  │ + enableEcoMode()    │                                         │
│  └──────────────────────┘                                         │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📝 KETERANGAN SIMBOL

- `+` = Public
- `-` = Private
- `#` = Protected
- `{abstract}` = Abstract Method
- `<<abstract>>` = Abstract Class
- `───►` = Association (Asosiasi)
- `───▷` = Inheritance (Pewarisan)
- `───◆` = Composition (Komposisi)
- `───◇` = Aggregation (Agregasi)

---

## 💡 CATATAN IMPLEMENTASI

1. **Sensor Package**: Menggunakan Abstract Class untuk memastikan semua sensor memiliki interface yang konsisten
2. **Light Control**: Memisahkan entitas Room dan Light untuk fleksibilitas pengelolaan
3. **Notification**: Menggunakan Manager pattern untuk sentralisasi pengelolaan notifikasi
4. **Dashboard**: Menggunakan Widget pattern untuk modularitas komponen UI

---

**Generated:** December 16, 2025  
**System:** Exeliora Smart Home System  
**Version:** 1.0
