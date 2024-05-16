import 'package:flutter/material.dart';
import 'package:mou_app/ui/base/base_widget.dart';
import 'package:mou_app/ui/feedback/feedback_viewmodel.dart';
import 'package:mou_app/ui/widgets/app_body.dart';
import 'package:mou_app/ui/widgets/app_content.dart';
import 'package:mou_app/ui/widgets/loading_full_screen.dart';
import 'package:mou_app/ui/widgets/menu/app_menu_bar.dart';
import 'package:mou_app/utils/app_colors.dart';
import 'package:mou_app/utils/app_constants.dart';
import 'package:mou_app/utils/app_images.dart';
import 'package:provider/provider.dart';

class FeedbackPage extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return BaseWidget<FeedbackViewModel>(
      viewModel: FeedbackViewModel(userRepository: Provider.of(context)),
      builder: (context, viewModel, child) => StreamBuilder<bool>(
        stream: viewModel.loadingSubject,
        builder: (context, snapshot) {
          return LoadingFullScreen(
            loading: snapshot.data,
            child: Scaffold(
              key: viewModel.scaffoldKey,
              body: AppBody(
                child: AppContent(
                  menuBarBuilder: (stream) => AppMenuBar(tabObserveStream: stream),
                  headerImage: AssetImage(AppImages.bgCalendarNew),
                  headerBuilder: (hasInternet) => _buildAppBar(context, viewModel, hasInternet),
                  childBuilder: (_) => _buildContent(context, viewModel),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildAppBar(BuildContext context, FeedbackViewModel viewModel, bool hasInternet) {
    return Container(
      height: AppConstants.appBarHeight,
      width: double.infinity,
      alignment: Alignment.center,
      child: Padding(
        padding: const EdgeInsets.only(left: 16, right: 16, top:22),
        child: Stack(
          alignment: Alignment.center,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(
                  constraints: const BoxConstraints(maxWidth: 30),
                  icon: Image.asset(
                    AppImages.icArrowRight,
                    width: 12,
                  ),
                  onPressed: () => Navigator.pop(context),
                ),
                Container(
                  width: 42,
                  padding: const EdgeInsets.only(right: 12),
                  child: StreamBuilder<bool>(
                    stream: viewModel.hasSaveSubject,
                    builder: (context, snapshot) {
                      final hasSave = snapshot.data ?? false;
                      return hasSave && hasInternet
                          ? IconButton(
                              icon: Image.asset(
                                AppImages.icAccept,
                                height: 14,
                              ),
                              padding: EdgeInsets.zero,
                              onPressed: viewModel.addFeedBack,
                            )
                          : const SizedBox();
                    },
                  ),
                ),
              ],
            ),
            Image.asset(
              AppImages.icFeedBackG,
              width: 44,
            )
          ],
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, FeedbackViewModel viewModel) {
    return Container(
      constraints: BoxConstraints.expand(),
      decoration: BoxDecoration(
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.2),
            blurRadius: 4,
            offset: Offset(2, 5),
          )
        ],
        borderRadius: BorderRadius.all(Radius.circular(15)),
        color: Colors.white,
      ),
      margin: const EdgeInsets.fromLTRB(18, 6, 24, 24),
      child: TextFormField(
        controller: viewModel.textController,
        maxLines: 20,
        style: TextStyle(color: AppColors.normal),
        decoration: InputDecoration(
          contentPadding: const EdgeInsets.all(10),
          border: InputBorder.none,
          focusedBorder: InputBorder.none,
        ),
      ),
    );
  }
}
