import 'package:flutter/material.dart';
import 'package:kampilya_admin_webview/webview_main.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Kampilya Admin',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        appBarTheme: AppBarTheme(
          backgroundColor: Colors.white,
          iconTheme: IconThemeData(color: Colors.black),
          titleTextStyle: TextStyle(color: Colors.black),
        ),
      ),
      home: FullFeatureWebView(
        initialUrl: 'https://admin.kampilya.com',
        headers: {'Custom-Header': 'Value'},
      ),
    );
  }
}
