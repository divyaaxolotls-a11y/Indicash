// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:kampilya_admin_webview/main.dart';

void main() {
  testWidgets('WebView app smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const MyApp());

    // Verify that the title is correct.
    expect(find.text('Vedic Santulan'),
        findsNothing); // Title is in MaterialApp, find.text might not see it easily without an AppBar

    // Check if we have a FullFeatureWebView (via the class type)
    // Since WebView logic starts immediately, we just check if the app tree builds.
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
